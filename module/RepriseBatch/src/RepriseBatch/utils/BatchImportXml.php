<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RepriseBatch\utils;

use RepriseBatch\Model\Pays;
use RepriseBatch\Model\User;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\ServiceManager\ServiceManager;

// REFAIRE TOUS PAR RAPPORT A LA CLASSE BATCH


class BatchImportXml extends \XMLReader {
    
    private $forced;
    private $filexml;
    private $config;
    private $archive_url;
    private $erreur_url;
    private $serviceloc;
    private $poidsMaxi = "";
    private $heurelimit = "";
    private $heurelimitfin = "";

    /**
     * constructeur
     * @param  ServiceManager $serviceloc manager de service
     * @param bool $forced
     * @throws \Exception
     */
    public function __construct(ServiceManager $serviceloc, $forced = false) {
        $this->forced = $forced;
        $this->serviceloc = $serviceloc;
        $config = $serviceloc->get('Config');

        if(!array_key_exists('application_config', $config) || !array_key_exists('import_xml', $config['application_config']) || !array_key_exists('url_fichier', $config['application_config']['import_xml']) || !file_exists($config['application_config']['import_xml']['url_fichier'])) {
            throw new \Exception("File xml doesn't exists");            
        }
        $this->filexml = $config['application_config']['import_xml']['url_fichier'];

        if(!array_key_exists('url_fichier_config', $config['application_config']['import_xml']) || !file_exists($config['application_config']['import_xml']['url_fichier_config'])) {
            throw new \Exception("File for import configuration doesn't exists");            
        }
        $this->config = require($config['application_config']['import_xml']['url_fichier_config']);

        if(!array_key_exists('url_archive', $config['application_config']['import_xml'])) {
            throw new \Exception("Archive directory doesn't exists");            
        }
        $this->archive_url = $config['application_config']['import_xml']['url_archive'].DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR.date('Y-m').DIRECTORY_SEPARATOR.date('Y-m-d-H-i-s').DIRECTORY_SEPARATOR;
        @mkdir($this->archive_url, 0777, true);
        $this->erreur_url = $config['application_config']['import_xml']['url_erreur'].DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR.date('Y-m').DIRECTORY_SEPARATOR.date('Y-m-d-H-i-s').DIRECTORY_SEPARATOR;

        if(!array_key_exists('poids_maxi', $config['application_config']['import_xml'])) {
            throw new \Exception("Property for max file size doesn't exists");            
        }
        $this->poidsMaxi = $config['application_config']['import_xml']['poids_maxi'];

        if(!array_key_exists('heure_limit', $config['application_config']['import_xml'])) {
            throw new \Exception("Property for max file size doesn't exists");            
        }
        $this->heurelimit = \DateTime::createFromFormat('H:i:s', $config['application_config']['import_xml']['heure_limit']);

        if(!array_key_exists('heure_limit_fin', $config['application_config']['import_xml'])) {
            throw new \Exception("Property for max file size doesn't exists");
        }
        $this->heurelimitfin = \DateTime::createFromFormat('H:i:s', $config['application_config']['import_xml']['heure_limit_fin']);
    }

    /**
    * function qui va instancier le traitement des fichiers xml
    * elle va verifier que le chemin fournit est un fichier, si c"est un repertoire, elle va tourner en boucle sur celui-ci pour prendre chacuns des fichiers par ordre de DATE de modification ASC, 
    * et elle archive les fichiers
    */
    public function init() {
        if(is_dir($this->filexml)) {

            $listFile = scandir($this->filexml);

            // filter and sort by mtime
            $tab = array();
            $mtimemax = time() - 30; // on ne traite un fichier que s'il a été déposé il y a plus de 30 secondes
            foreach ($listFile as $value) {
                if (is_file($this->filexml.'/'.$value) && strtolower(substr($value, -4)) == ".xml") {
                    $mtime = filemtime($this->filexml.'/'.$value);
                    if ($mtime < $mtimemax) {
                        while(array_key_exists($mtime, $tab)) {
                            $mtime++;
                        }
                        $tab[$mtime] = $value;
                    }
                }
            }
            ksort($tab);

            foreach ($tab as $value) {
                $this->handlingFile($this->filexml.'/'.$value);
            }
        } else {
            $this->handlingFile($this->filexml);
        }
        
    }

    private function isFileSizeOk($file) {
        if($this->forced) {
            return true;
        }
        if (filesize($file) < $this->poidsMaxi) {
            return true;
        }
        $now = new \DateTime();
        if ($this->heurelimitfin < $this->heurelimit) {
            return $now >= $this->heurelimit || $now <= $this->heurelimitfin;
        } else {
            return $now >= $this->heurelimit && $now <= $this->heurelimitfin;
        }
    }

    /**
     * function qui va peremttre le traitement d'un fichier xml
     * avec archivage de celui ci
     * @param string $filename nom du fichier
     * @throws \Exception
     * @return void
     */
    private function handlingFile($filename) {

        if (!$this->isFileSizeOk($filename)) {
            throw new \Exception("Fichier trop gros pour etre traite pour '".$this->filexml."'(".filesize($filename)." > ".$this->poidsMaxi.")");
        }

        // deplacement vers le fichier d'archive
        $file = $this->archive_url.basename($filename);
        rename($filename, $file);

        $this->serviceloc->get('logger')->logApp("batch ImportXML, debut traitement du fichier '$file'");

        // traitement du fichier
        $this->traitementBatch($file);

        // log pour indiquer la fin du triatment du fichier
        $this->serviceloc->get('logger')->logApp("batch ImportXML, Fin traitement du fichier '$file'");
    }


    /**
     *   function pour le traitement quotidien des xml
     * @param  string $filexml file name
     * @throws \Exception
     * @return void
     */
    private function traitementBatch($filexml) {
        if(! $this->open($filexml)){
            throw new \Exception("Unable to open the xml file : $filexml");            
        }

        if(empty($this->config)){
            throw new \Exception("We don't have any configuration");
        }

        try{
            $facCarac = $this->serviceloc->get("RepriseBatch\Model\CaracteristicTable");
            // definiition des balise a checker ou non 
            $baliseCheck = array();
            $baliseCampingCheck = array();
            $detailsbaliseCampingCheck = array();
            $baliseNoCheck = array();
            $baliseNotraite = array();
            $baliseNoTourve = array();
            $traitementBalise = array();
            $listCaraToCheckForGrp = array();
            $arrtibut_to_check = array();
            $listeCaracSuffixe = array();
            $millesime = array();

            // recuperation de la liste des cara a surveiller
            $temptabcarac = $facCarac->getCaracLinkedGroupCara();
            foreach ($temptabcarac as $key => $value) {
                $listCaraToCheckForGrp[strtolower($value['attribute_name'])] = $value;
            }
            $arrtibut_to_check = array_keys($listCaraToCheckForGrp);

            // recuperation des suffixe
            $templistsuffixe = $facCarac->getCaracteristicSuffixe();
            foreach ($templistsuffixe as $key => $value) {
                if(!array_key_exists(strtolower($value->suffix_xml_tag_year), $listeCaracSuffixe)) {
                    $listeCaracSuffixe[strtolower($value->suffix_xml_tag_year)] = array();
                }
                $listeCaracSuffixe[strtolower($value->suffix_xml_tag_year)][] = $value->id;
            }

            if(array_key_exists("balise_a_pas_prendre", $this->config)) {
                $baliseNoCheck = $this->config['balise_a_pas_prendre'];
            }

            if(array_key_exists("balise_a_prendre", $this->config)) {
                foreach ($this->config['balise_a_prendre'] as $key => $value) {
                    $baliseCheck[] = $value['libelle'];
                    $traitementBalise[$value['libelle']] = array(
                        "factoryclasse" => $value['factoryclasse'],
                        "classe" => $value['classe'],
                        "attribute" => $value['attribute'],
                        "attribute_join" => (array_key_exists("attribute_join", $value))?$value['attribute_join']: null,
                    );
                }
            }

            // recuperation des balise camping a traite
            $classe = $this->serviceloc->get('RepriseBatch\Model\CaracteristicTable');
            $tabcampbal =  $classe->fetchAll();
            if(is_object($tabcampbal)) {
                foreach ($tabcampbal as $key => $value) {
                    $baliseCampingCheck[] = $value->xml_tag;
                    $detailsbaliseCampingCheck[$value->xml_tag] = $value;
                } 
            }

            // lecture en boucle jusqu'a la fin
            $depth = 1;
            $text = "";

            // sauvegade pour les donnees en cours
            $pays_sauv = 0;
            $regi_sauv = 0;
            $dept_sauv = 0;
            $comm_sauv = 0;
            $camp_sauv = 0;
            $camp_supp_passe = false;
            $camp_clas_passe = false;
            $listeBaliseUSedForCamping = array();

            while ($this->read() && $depth != 0)
            {
                if($this->nodeType != \XMLReader::END_ELEMENT) {
                    if(is_array($baliseNoCheck) && in_array($this->name, $baliseNoCheck)) { // si ce sont des balises a ne pas traite, on ne fait rien 
                    } elseif(is_array($baliseCheck) && in_array($this->name, $baliseCheck)) {// si ce sont des balise a traite
                        // variable necessaire pour le traitement
                        $baliseName = $this->name;
                        $is_exist = false;
                        $obj = null;
                        $sav_obj = null;
                        $listXmlAttUsed = array_keys($traitementBalise[$baliseName]['attribute']);

                        // on va verifier que l'objet n existe pas en base (generique pour les pays, region, dept, et camping)
                        $classe = $this->serviceloc->get('RepriseBatch\Model\\'.$traitementBalise[$baliseName]['factoryclasse']);
                        
                        /*
                            Pour les Découpages, nous allons vérifier en base, s'il existe un autre découpage avec le même code.
                            Si tel est le cas, nous faisons une première étape pour savoir si ID xml de celui-ci correspond à celui de notre base,
                            deux résultats possibles:

                                - c'est le même: nous récupérons les informations du découpage dans le xml, pour mettre à jour notre base de données
                                - il est différent: nous ne faisons rien. La seconde étape consistera à prendre ce découpage comme "département"
                                pour les communes du noeud xml.
                        */
                        $tmp = null;
                        $modification_decoupage = true;
                        if($baliseName == "Decoupage") {
                            $tmp = $classe->getByCode($this->getAttribute("Code"));
                            if($tmp->count() == 0) {
                                $tmp = $classe->getByRef($this->getAttribute("Id"));
                            } else if($tmp->current()->reference != $this->getAttribute("Id")) {
                                $modification_decoupage = false;
                            }

                        } else {
                            $tmp = $classe->getByRef($this->getAttribute("Id"));                        
                        }

                        if($tmp->count() != 0) {
                            $is_exist = true;
                            $obj = $tmp->current();
                        } else {
                            $cls = 'RepriseBatch\Model\\'.$traitementBalise[$baliseName]['classe'];
                            $obj = new $cls();
                            if($baliseName == "Site") {
                                $obj->status = 1;
                            }
                        }

                        // sauvegarde du l'object si celui-ci est un camping 
                        if($obj->id != "" && $obj->id != null) {
                            $sav_obj = clone($obj);
                        }

                        if($baliseName != "Decoupage" || ($baliseName == "Decoupage" && $modification_decoupage == true)) {
                            // mise a jour de toutes les informations de l'objet 
                            foreach ($traitementBalise[$baliseName]['attribute'] as $key => $value) {
                                if($this->getAttribute($key) === NULL) {
                                    $baliseNoTourve[$baliseName] =  $key;
                                } else {
                                    // traitement pour les longitude et latitude
                                    if($key == "Latitude" || $key == "Longitude" ) {
                                        $tmp = $this->getAttribute($key).' ';
                                        $tmp = substr((str_replace(',', '.', $tmp)),0,10);
                                        $obj->$value = "".$tmp;
                                    } elseif($key == "CpGeo" && $baliseName == "Site") {
                                        $obj->$value =  str_pad($this->getAttribute($key), 5, "0", STR_PAD_LEFT);
                                    } else {
                                        $obj->$value =  $this->getAttribute($key);
                                    }
                                }
                            }

                            // si dans la configuration nous avons ajouter des parametres necesitant plusieurs attributs, nous les traitons
                            if($traitementBalise[$baliseName]['attribute_join'] != null && is_array($traitementBalise[$baliseName]['attribute_join'])) {
                                foreach ($traitementBalise[$baliseName]['attribute_join'] as $key => $value) {
                                    $text = "";
                                    foreach ($value as $k => $v) {
                                        if($this->getAttribute($v) !== NULL) {
                                            $text .= $this->getAttribute($v).' ';
                                        }
                                    }

                                    $obj->$key = $text;
                                }
                            }
                        }

                        // cas particulier des region, dept...
                        if($baliseName == "Region") {
                            $obj->country_id = $pays_sauv;
                        } elseif($baliseName == "Decoupage") {
                            $obj->region_id = $regi_sauv;
                        } elseif($baliseName == "Commune") {
                            $obj->department_id = $dept_sauv;
                        } elseif($baliseName == "Site") {
                            $obj->city_id = $comm_sauv;
                        } 

                        /*
                            - dans le cas d'un camping
                            => lors de l'import d'un camping, pour un camping sans coordonnées gps dans le xml, prendre les coordonnées gps de la commune (en base de données)
                        */
                        if($baliseName == "Site" && (is_null($obj->coord_latitude) || $obj->coord_latitude == "" || is_null($obj->coord_longitude) || $obj->coord_longitude == "")) {
                            if(!empty($obj->city_id)) {
                                $cityTable = $this->serviceloc->get('RepriseBatch\Model\CityTable');
                                $tempobjcity = $cityTable->getCommune($obj->city_id);
                                if(is_object($tempobjcity)) {
                                    $obj->coord_latitude = $tempobjcity->coord_latitude;
                                    $obj->coord_longitude = $tempobjcity->coord_longitude;
                                }
                            }
                        }

                        // check dans le cas d'un camping pour verifier qu'il y a bien eux une modification
                        if($obj->id != "" && $obj->id != null) {
                            $test = $obj->isDiff($sav_obj);
                            if($test === true) {
                                // sauvegarde uniquement si nous avons une modification de decoupage
                                if($baliseName != "Decoupage" || ($baliseName == "Decoupage" && $modification_decoupage == true)) {
                                    $obj = $classe->save($obj);  
                                }
                            }
                        } else {
                            // sauvegarde uniquement si nous avons une modification de decoupage
                            if($baliseName != "Decoupage" || ($baliseName == "Decoupage" && $modification_decoupage == true)) {
                                $obj = $classe->save($obj);
                            }
                        }
                    
                        // enregistrement du gestionnaire du camping
                        if($baliseName == "Site") {
                            $this->createCampingManager($obj->reference, $obj->id, $this->getAttribute("Password"), $this->getAttribute("Gestionnaire"));
                        }

                        while($this->moveToNextAttribute()) {
                            if(! in_array($this->name, $listXmlAttUsed) && (! array_key_exists($baliseName, $baliseNotraite) || !array_key_exists($this->name, $baliseNotraite[$baliseName]))) {
                                $this->log($this->name, $baliseName);
                            }
                        }


                        // suppression de toutes les caracteristiques du camping precedent qui ont disparus
                        if($camp_sauv != 0 && $camp_sauv != $obj->id && $baliseName == "Site") {
                            // nous allons traite le cas du camping precedent
                            $this->washCampingCaracteristic($camp_sauv, $listeBaliseUSedForCamping);
                            $listeBaliseUSedForCamping = array();

                            // nous allons mettre a jour la table _opening_date
                            $this->washCampingOpeningDate($camp_sauv);

                            // si le camping n'a pas la balise fermeture, on verifie qu'il est bien a jour en base
                            if($camp_supp_passe == false) {
                                $this->checkCampingActive($camp_sauv);
                            } else {
                                $camp_supp_passe = false;                            
                            }

                            // mise a jour du classement du camping
                            if($camp_clas_passe == false) {
                                $this->checkCampingRating($camp_sauv);
                            } else {
                                $camp_clas_passe = false;                            
                            }

                            // mise a jour de l'attribut mail du camping
                            $this->updateCampingMail($camp_sauv);
                        }
                        

                        // sauvegarde des donnee
                        if($baliseName == "Pays") {
                            $pays_sauv = $obj->id;
                        } elseif($baliseName == "Region") {
                            $regi_sauv = $obj->id;
                        } elseif($baliseName == "Decoupage") {
                            $dept_sauv = $obj->id;
                        } elseif($baliseName == "Commune") {
                            $comm_sauv = $obj->id;
                        } elseif($baliseName == "Site") {
                            $camp_sauv = $obj->id;
                        }

                        // recherche des millesime dans le cas ou nous sommes sur un camping
                        if($baliseName == "Site") {
                            $millesime = array();

                            foreach ($listeCaracSuffixe as $suffixe => $listidacarsuff) {
                                // par default nous mettons la valeur de la configuration globale
                                $mil_save = MILLESIME_YEAR;

                                $classe = $this->serviceloc->get('RepriseBatch\Model\CampingCaracteristicTable');
                                $textInnerXml = $this->readInnerXML();
                                /*
                                 * si taranc dans le xml, alors on prends la valeurs de la constante MILLESIME_TARANC
                                 * si tarnouv dans le xml, alors on prends la valeurs de la constante MILLESIME_TARNOUV
                                 */
                                if(!empty($textInnerXml) && strpos(strtolower($textInnerXml), strtolower("taranc".$suffixe)) !== false) {
                                    $mil_save = MILLESIME_TARANC;
                                } else if(!empty($textInnerXml) && strpos(strtolower($textInnerXml), strtolower("tarnouv".$suffixe)) !== false) {
                                    $mil_save = MILLESIME_TARNOUV;
                                }

                                foreach($listidacarsuff as $idacarsuff) {
                                    $millesime[$idacarsuff] = $mil_save;
                                }
                            }
                        }

                    } elseif(is_array($baliseCampingCheck) && in_array($this->name, $baliseCampingCheck)) {// si ce sont des balise des camping
                        $baliseName = $this->name;

                        // cas sepcifique de la fermeture d'un camping 
                        if($baliseName == "FERMETURE") {
                            $camp_supp_passe = true;
                            $fac = $this->serviceloc->get('RepriseBatch\Model\CampingTable');
                            $tmp = $fac->getCamping($camp_sauv);
                            if(is_object($tmp) && $tmp->id == $camp_sauv) {
                                $tmp->status = 0;
                                $fac->save($tmp);
                            }                                
                        } else {
                            // recuperation de tous le noeuds xml,
                            $tmp = $this->readString();
                            $p = xml_parser_create();
                            xml_parse_into_struct($p, $this->readOuterXML(), $vals);
                            xml_parser_free($p);
                            
                            $attribute_keys = array();
                            $attribute = array();
                            $att_trouve = false;
                            if (!(! is_array($vals) || count($vals) < 1 || !array_key_exists("attributes", $vals[0]) || !is_array($vals[0]['attributes']))) {
                                $attribute_keys = array_keys($vals[0]['attributes']);
                                $attribute = $vals[0]['attributes'];
                            
                                //si le noeud possède l'un des attributs specifiques, sauvegarde des camping_caracteristic
                                foreach ($attribute_keys as $attr_name) {
                                    if(in_array(strtolower($attr_name), $arrtibut_to_check)) {
                                        $att_trouve = true;
                                    }
                                }
                            }
                            
                            $classe = $this->serviceloc->get('RepriseBatch\Model\CampingCaracteristicTable');

                            if($att_trouve) {
                                // nous creons autant de campings caracterisitc que d'attribut specifique ayant une valeur != "N"
                                $list_campingcarac_to_create = array();
                                $listeAttrToAdd = array();
                                foreach ($attribute as $attribute_name => $attribute_value) {
                                    if(in_array(strtolower($attribute_name), $arrtibut_to_check)) {
                                        if($attribute_value != "N") {
                                            $cls = 'RepriseBatch\Model\CampingCaracteristic';
                                            $obj = new $cls();
                                            $obj->camping_id = $camp_sauv;
                                            $obj->caracteristic_id = $detailsbaliseCampingCheck[$baliseName]->id;
                                            $obj->group_caracteristics_id = $listCaraToCheckForGrp[strtolower($attribute_name)]['id'];
                                            $prop = strtolower($attribute_name);
                                            $obj->$prop = $attribute_value;
                                            // si la caracteristique a un suffixe, on tente de la recuperer, sinon null
                                            $obj->year = (! empty($detailsbaliseCampingCheck[$baliseName]->suffix_xml_tag_year))?((array_key_exists($obj->caracteristic_id, $millesime))?$millesime[$obj->caracteristic_id]:MILLESIME_YEAR):NULL;


                                            $list_campingcarac_to_create[] = $obj;
                                        }
                                    } else {
                                        $listeAttrToAdd[strtolower($attribute_name)] = $attribute_value;
                                    }
                                }
                                foreach ($list_campingcarac_to_create as $objToSave) {
                                    foreach ($listeAttrToAdd as $attribute_name => $attribute_value) {
                                        if(property_exists($objToSave, strtolower($attribute_name))) {
                                            $prop = strtolower($attribute_name);
                                            $objToSave->$prop = $attribute_value;
                                        }
                                    }

                                    $objToSave = $classe->save($objToSave);
                                    $listeBaliseUSedForCamping[] = $objToSave->id;
                                }

                                // on enregistre tous de meme la balise
                                if(strpos($baliseName, "TARANC") !== false || strpos($baliseName, "TARNOUV") !== false ) {
                                    $cls = 'RepriseBatch\Model\CampingCaracteristic';
                                    $obj = new $cls();
                                    $obj->camping_id = $camp_sauv;
                                    $obj->caracteristic_id = $detailsbaliseCampingCheck[$baliseName]->id;
                                    $obj->group_caracteristics_id = $detailsbaliseCampingCheck[$baliseName]->group_caracteristics_id;
                                    // si la caracteristique a un suffixe, on tente de la recuperer, sinon null
                                    $obj->year = (! empty($detailsbaliseCampingCheck[$baliseName]->suffix_xml_tag_year))?((array_key_exists($obj->caracteristic_id, $millesime))?$millesime[$obj->caracteristic_id]:MILLESIME_YEAR):NULL;
                                    $obj = $classe->save($obj);
                                    $listeBaliseUSedForCamping[] = $obj->id;
                                }

                            } else {// sinon enregristrement du camping camracterisitc
                                $cls = 'RepriseBatch\Model\CampingCaracteristic';
                                $obj = new $cls();
                                $obj->camping_id = $camp_sauv;
                                $obj->caracteristic_id = $detailsbaliseCampingCheck[$baliseName]->id;
                                $obj->group_caracteristics_id = $detailsbaliseCampingCheck[$baliseName]->group_caracteristics_id;

                                // si la caracteristique a un suffixe, on tente de la recuperer, sinon null
                                $obj->year = (! empty($detailsbaliseCampingCheck[$baliseName]->suffix_xml_tag_year))?((array_key_exists($obj->caracteristic_id, $millesime))?$millesime[$obj->caracteristic_id]:MILLESIME_YEAR):NULL;
                                
                                foreach ($attribute as $attribute_name => $attribute_value) {
                                    if(property_exists($obj, strtolower($attribute_name))) {
                                        $prop = strtolower($attribute_name);
                                        $obj->$prop = $attribute_value;

                                        // si c est une propriete AD, nous allons verifier que la caraceristique est une label_ad, si tel est le cas, nous remplacons la valeur de la propriete
                                        if($prop == "ad") {
                                            if($detailsbaliseCampingCheck[$baliseName]->label_ad != null && $detailsbaliseCampingCheck[$baliseName]->label_ad != "") {
                                                $obj->$prop = $detailsbaliseCampingCheck[$baliseName]->label_ad;
                                            }
                                        }       
                                        
                                        //Champs FEELEC ou ELEC1 ou ELEC2 ou SUPELEC1 ou SUPELEC2 : si attribut remarque, on ajoute « A » derrière
                                        if(($baliseName == "FEELEC" || $baliseName == "ELEC1" || $baliseName == "ELEC2" || $baliseName == "SUPELEC1" || $baliseName == "SUPELEC2") && $prop == "remarque") {
                                            if( $attribute_value != "") {
                                                $obj->$prop = $obj->$prop." A";
                                            }
                                        }

                                        //Champs FEENFANT OU ENFANT : si attribut remarque, on ajoute « ans » derrière
                                        if(($baliseName == "FEENFANT" || $baliseName == "ENFANT") && $prop == "remarque") {
                                            if( $attribute_value != "") {
                                                $obj->$prop = $obj->$prop." ans";
                                            }
                                        }

                                        // si c est une propriete CLASSEMNT, nous allons mettre a jour le camping
                                        if($baliseName == "CLASSEMNT" && $prop == "val") {
                                            $camp_clas_passe = true;
                                            $fac = $this->serviceloc->get('RepriseBatch\Model\CampingTable');
                                            $tmp = $fac->getCamping($camp_sauv);
                                            if(is_object($tmp) && $tmp->id == $camp_sauv) {
                                                $matches = array();
                                                preg_match_all('/\*/', $obj->$prop, $matches, PREG_SET_ORDER);
                                                $tmp->rating = count($matches);
                                                $fac->save($tmp);
                                            }                                
                                        }
                                    }
                                }
                                // sauvegarde de l'element
                                $obj = $classe->save($obj);
                                $listeBaliseUSedForCamping[] = $obj->id;
                            }
                        }
                    } else {
                        if(! array_key_exists($this->name, $baliseNotraite)) {
                            //$baliseNotraite['liste_balise'][$this->name] = $this->name;
                            $this->log($this->name, "liste_balise");
                        }
                    }
                }
                if (in_array($this->nodeType, array(\XMLReader::TEXT, \XMLReader::CDATA, \XMLReader::WHITESPACE, \XMLReader::SIGNIFICANT_WHITESPACE)))
                    $text .= $this->value;
                // test pour savoir si nous somme revenu a la fin de balise
                if ($this->nodeType == \XMLReader::ELEMENT) {
                    $depth++;
                }
                if ($this->nodeType == \XMLReader::END_ELEMENT) {
                    $depth--;
                }
            }

            // suppression de toutes les caracteristiques du camping precedent qui ont disparus
            if($camp_sauv != 0) {
                // nous allons traite le cas du camping precedent
                $this->washCampingCaracteristic($camp_sauv, $listeBaliseUSedForCamping);

                // nous allons mettre a jour la table _opening_date
                $this->washCampingOpeningDate($camp_sauv);

                // si le camping n'a pas la balise fermeture, on verifie qu'il est bien a jour en base
                if($camp_supp_passe == false) {
                    $this->checkCampingActive($camp_sauv);
                }

                // mise a jour du classement du camping
                if($camp_clas_passe == false) {
                    $this->checkCampingRating($camp_sauv);
                }

                // mise a jour de l'attribut mail du camping
                $this->updateCampingMail($camp_sauv);
            }

            // verification puis modification des lien thematic camping dans le cas de la creation d'un camping ou de la modification d'un camping
            $classe = $this->serviceloc->get('RepriseBatch\Model\CampingTable');
            $classe->checkThematicLink();

            return $baliseNotraite;
        } catch(\Exception $e) {
            $this->serviceloc->get('logger')->logApp("batch ImportXML, ERREUR : ".$e->getMessage());
            if(! file_exists($this->erreur_url)){
                @mkdir($this->erreur_url, 0777, true);
            }
            if (!copy($filexml, $this->erreur_url.basename($filexml))) {
                $this->serviceloc->get('logger')->logApp("batch ImportXML, ERREUR : La copie du fichier '$filexml' a échoué...Hors ce fichier est en erreur");
            }
        }

    }

    /**
     * function to log
     * @param  string $txt text to log
     * @param $file
     * @throws \Exception
     * @return void
     */
    private function log($txt, $file) {
        static $repositoryLog = null;
        static  $loggers = array();
        if (!isset($loggers[$file])) {
            if (is_null($repositoryLog)) {
                $repositoryLog = __DIR__ .'/../../../log/'.date("y-m-d_H_i_s");
                if (!mkdir($repositoryLog, 0777, true)) {
                    throw new \Exception('Failed to create folders...');
                }
            }
            $writer = new Stream($repositoryLog.'/'.$file."_".date( 'Y-m-d' ).'-info.log');
            $loggers[$file] = new Logger();
            $loggers[$file]->addWriter($writer);

        }
        $loggers[$file]->log(1, $txt."\n\r");
    }

    /**
    * function to wash camping caracteristics
    * @param int   $idCamp     camping identifier
    * @param array $listCarac  list of caracteristics' identifier 
    * @return void
    */
    private function washCampingCaracteristic($idCamp, array $listCarac) {
        $fac = $this->serviceloc->get('RepriseBatch\Model\CampingCaracteristicTable');
        $fac->deleteNotSave($idCamp, $listCarac);
    }


    /**
    * function to wash camping opening date
    * @param int   $idCamp     camping identifier
    * @return void
    */
    private function washCampingOpeningDate($idCamp) {
        $fac = $this->serviceloc->get('RepriseBatch\Model\CampingTable');
        $fac->updateOpeningDate($idCamp);
    }

    /**
    * function to create camping manager
    * @param int   $idXmlCamping camping identifier
    * @param int   $idCamp       camping identifier
    * @param sting $password     password for manager
    * @return void
    */
    private function createCampingManager($idXmlCamping, $idCamp, $password, $name = null) {
        $obj = null;
        $isExiste = false;

        $fac = $this->serviceloc->get('RepriseBatch\Model\UserTable');
        $rep = $fac->getCampingManager($idCamp);
        if($rep == null || !is_object($rep)) {
            $obj = new User();
        } else {
            $obj = $rep;
            $isExiste = true;
        }

        $obj->login = $idXmlCamping;
        $obj->password = $password;
        $obj->name = $name;
        $obj->created_at = date('y-m-d H:i:s');
        $obj->updated_at = date('y-m-d H:i:s');

        
        $obj = $fac->save($obj);
        if($isExiste == false) {
            $fac->linkToCamping($obj, $idCamp);
            
            // mise a jour du role du user
            $fac->saveRoleGestionnaire($obj);
        }
    }

    /**
    * function active a camping
    * @param int   $idCamp       camping identifier
    * @return void
    */
    private function checkCampingActive($idCamp) {
        // si le camping n'a pas la balise fermeture, on verifie qu'il est bien a jour en base
        $fac = $this->serviceloc->get('RepriseBatch\Model\CampingTable');
        $tmp = $fac->getCamping($idCamp);
        if(is_object($tmp) && $tmp->id == $idCamp && $tmp->status == 0) {
            $tmp->status = 1;
            $fac->save($tmp);
        } 
    }

    /**
    * function update a camping rating
    * @param int   $idCamp       camping identifier
    * @return void
    */
    private function checkCampingRating($idCamp) {
        // si le camping n'a pas la balise fermeture, on verifie qu'il est bien a jour en base
        $fac = $this->serviceloc->get('RepriseBatch\Model\CampingTable');
        $tmp = $fac->getCamping($idCamp);
        if(is_object($tmp) && $tmp->id == $idCamp && $tmp->rating != null) {
            $tmp->rating = null;
            $fac->save($tmp);
        } 
    }


    /**
     * function for update campings email attribut
     * @param  int  $idCamp
     * @return void
     */
    private function updateCampingMail($idCamp) {
        $fac = $this->serviceloc->get('RepriseBatch\Model\CampingTable');
        $fac->updateCampingMail($idCamp);
    }
}
