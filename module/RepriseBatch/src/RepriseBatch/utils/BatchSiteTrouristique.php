<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RepriseBatch\utils;

use RepriseBatch\Model\Partner;
use RepriseBatch\Model\Contract;
use RepriseBatch\Service\BatchCSV;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\ServiceManager\ServiceManager;


class BatchSiteTrouristique extends BatchCSV {
    /**
    * constructeur
    * @param  ServiceManager $serviceloc manager de service
    * @throws Exception
    */

    private $listLienReservationPartner;
    private $listContractPartner;
    private $doublonContract = array();

    public function __construct(ServiceManager $serviceloc) {
        $this->serviceloc = $serviceloc;
        $config = $serviceloc->get('Config');

        if(!array_key_exists('application_config', $config) || !array_key_exists('site_touristique', $config['application_config']) || !array_key_exists('url_fichier', $config['application_config']['site_touristique']) || !file_exists($config['application_config']['site_touristique']['url_fichier'])) {
            throw new \Exception("File CSV doesn't exists");            
        }
        $this->filecsv = $config['application_config']['site_touristique']['url_fichier'];

        if(!array_key_exists('carac_separateur', $config['application_config']['site_touristique'])) {
            throw new \Exception("We must to specify carac_separateur parameter");            
        }
        $this->carac_separateur = $config['application_config']['site_touristique']['carac_separateur'];

        if(!array_key_exists('line_depart', $config['application_config']['site_touristique'])) {
            throw new \Exception("We must to specify line_depart parameter");            
        }
        $this->line_depart = $config['application_config']['site_touristique']['line_depart'];

        if(!array_key_exists('url_archive', $config['application_config']['site_touristique'])) {
            throw new \Exception("Archive directory doesn't exists");            
        }
        $this->archive_url = $config['application_config']['site_touristique']['url_archive'].DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR.date('Y-m').DIRECTORY_SEPARATOR.date('Y-m-d-H-i-s').'_';
        @mkdir($this->archive_url, 0777, true);

        $this->erreur_url = $config['application_config']['site_touristique']['url_erreur'].DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR.date('Y-m').DIRECTORY_SEPARATOR.date('Y-m-d-H-i-s').'_';
        @mkdir($this->erreur_url, 0777, true);   
    }

    /**
    * function which update internet link for camping 
    * csv format:
    *    col 1 : camping_reference
    *    col 2 : code_langue
    *    col 3 : url
    *    col 4 : partner_name
    */
    protected function traitementBatch($file) {
        try{
            $dao4 = $this->serviceloc->get('RepriseBatch\Model\ThematicTable');
            $dao5 = $this->serviceloc->get('RepriseBatch\Model\CityTable');
            $arrayThematique = array();
            $arrayThematiqueCity = array();
                        
            // parcours du fichier ligne par ligne
            if (($handle = fopen($file, "r")) !== FALSE) {
                if(is_int($this->line_depart) && $this->line_depart != 0) {
                    while($this->line_depart != 0) {
                        fgets($handle, 4096);
                        $this->line_depart--;
                    }
                }

                /*
                    ON VA ENREGISTRER L ENSEMBLE DES LIAISON ENTRE THEMATIQUE DE TYPE THEMATIQUE(SITES TOURISTIQUE) ET CITY
                    pour ce faire, nous allons appliquer les regles suivantes:
                        - pour chaque thematique du fichier, nous allons supprimer toutes les laisons existantes qui n existent plus dans le fichier
                        - nous ne toucherons pas au liaisons entre thematiques et city si la thematique n est pas dans le fichier
                        - pour determiner une city, nous prenons l'id, s'il n existe pas, nous recherchons par rapport au nom et la region
                        - le fichier dois être de la forme suivante:
                            - ID COMMUNE 
                            - INSEE 
                            - CODE POSTAL 
                            - NOM COMMUNE 
                            - REGION 
                            - THEMATIQUE
                */
                while (($data = fgetcsv($handle, 1000, $this->carac_separateur)) !== FALSE) {
                    if(count($data) > 6) {
                        throw new \Exception("Bad file format");                    
                    }
                    try{
                        // recherche de la thematique
                        if(!array_key_exists($data[5], $arrayThematique)) {
                            $them = $dao4->getByLibelleOrigin($this->convert_smart_quotes((mb_detect_encoding($data[5]) != "UTF-8")?mb_convert_encoding($data[5], "UTF-8"):$data[5]));
                            $arrayThematique[$data[5]]['object'] = null;
                            $arrayThematique[$data[5]]['city'] = array();

                            $arrayThematique[$data[5]]['object'] = $them;
                            
                            // recherche de toutes les city lié
                            $themCity = $dao4->getCityByThematic($this->convert_smart_quotes((mb_detect_encoding($arrayThematique[$data[5]]['object']->id) != "UTF-8")?mb_convert_encoding($arrayThematique[$data[5]]['object']->id, "UTF-8"):$arrayThematique[$data[5]]['object']->id));
                            foreach ($themCity as $key => $value) {
                                $arrayThematique[$data[5]]['city'][$value->id] = $value;
                            }
                        }    
                        // test pour voir si la city avec cette reference exite
                        $listCityRef = $dao5->getByRef($data[0]);
                        $obj = $listCityRef->current(); // recuperation de la premiere ligne
                        if (!$obj) { // nous n'avons pas trouver de city avec cette reference
                            $obj = $dao5->findByLibelleAndLibRegion($this->convert_smart_quotes(((mb_detect_encoding($data[3]) != "UTF-8")?mb_convert_encoding($data[3], "UTF-8"):$data[3])), $this->convert_smart_quotes(((mb_detect_encoding($data[4]) != "UTF-8")?mb_convert_encoding($data[4], "UTF-8"):$data[4])));
                        }
                        
                        // si cette villes est dans la liste des villes de la thematique, nous ne faisons rien, sinon nous la creons
                        if(array_key_exists($obj->id, $arrayThematique[$data[5]]['city'])) {
                            $arrayThematique[$data[5]]['city'][$obj->id] = "mod";
                        } else {
                            $dao4->insertThematicCityLink($arrayThematique[$data[5]]['object']->id, $obj->id);
                        }
                    } catch(\Exception $e) {
                        $this->serviceloc->get('logger')->logApp("TRAITEMENT BATCH IMPORT CSV SITE TOURISTIQUE : ERROR : ".$e->getMessage());
                    }

                }
                fclose($handle);
            }

            foreach ($arrayThematique as $key => $value) {
                $idthem = $value['object']->id;
                foreach ($value['city'] as $k => $v) {
                    if($v != "mod" && is_object($v)) {
                        $dao4->deleteThematicCityLink($idthem, $v->id);
                    }
                }
            }
        } catch(\Exception $e) {
            if (!copy($file, $this->erreur_url.'/'.basename($file))) {
                throw new \Exception("La copie du fichier '$file' a échoué...Hors ce fichier est en erreur");
            }
        }
    }

    function convert_smart_quotes($string) { 
        $search = array(chr(145), 
                        chr(146), 
                        chr(147), 
                        chr(148), 
                        chr(151),
                        chr(192),
                        chr(193),
                        chr(194),
                        chr(195),
                        chr(196),
                        chr(197),
                        chr(198),
                        chr(199),
                        chr(200),
                        chr(201),
                        chr(202),
                        chr(203),
                        chr(204),
                        chr(205),
                        chr(206),
                        chr(207),
                        chr(210),
                        chr(211),
                        chr(212),
                        chr(213),
                        chr(214),
                        chr(215),
                        chr(216),
                        chr(217),
                        chr(218),
                        chr(219),
                        chr(220),
                        chr(221),
                        chr(222),
                        chr(223),
                        chr(224),
                        chr(225),
                        chr(226),
                        chr(227),
                        chr(228),
                        chr(229),
                        chr(230),
                        chr(231),
                        chr(232),
                        chr(233),
                        chr(234),
                        chr(235),
                        chr(236),
                        chr(237),
                        chr(238),
                        chr(239),
                        chr(240),
                        chr(241),
                        chr(242),
                        chr(243),
                        chr(244),
                        chr(245),
                        chr(246),
                        chr(248),
                        chr(249),
                        chr(250),
                        chr(251),
                        chr(252),
                        chr(253),
                        chr(254)); 

        $replace = array("'", 
                        "'", 
                        '"', 
                        '"', 
                        '-',
                        "à",
                        "a",
                        "â",
                        "ã",
                        "ä",
                        "a",
                        "ae",
                        "ç",
                        "è",
                        "é",
                        "ê",
                        "ë",
                        "i",
                        "i",
                        "î",
                        "ï",
                        "o",
                        "o",
                        "ô",
                        "õ",
                        "ö",
                        "x",
                        "",
                        "ù",
                        "u",
                        "û",
                        "ü",
                        "y",
                        "",
                        "",
                        "à",
                        "a",
                        "â",
                        "ã",
                        "ä",
                        "a",
                        "ae",
                        "ç",
                        "è",
                        "é",
                        "ê",
                        "ë",
                        "i",
                        "i",
                        "î",
                        "ï",
                        "o",
                        "ñ",
                        "o",
                        "o",
                        "ô",
                        "õ",
                        "ö",
                        "",
                        "u",
                        "u",
                        "û",
                        "ü",
                        "y",
                        ""); 

        return str_replace($search, $replace, $string); 
    }
}
