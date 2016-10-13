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
use RepriseBatch\Service\BatchCSV;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\ServiceManager\ServiceManager;


class BatchLogoPartenaire extends BatchCSV {
    /**
    * constructeur
    * @param  ServiceManager $serviceloc manager de service
    * @throws Exception
    */

    private $listLogoPartner = array();

    public function __construct(ServiceManager $serviceloc) {
        $this->serviceloc = $serviceloc;
        $config = $serviceloc->get('Config');

        if(!array_key_exists('application_config', $config) || !array_key_exists('logo_partenaire', $config['application_config']) || !array_key_exists('url_fichier', $config['application_config']['logo_partenaire']) || !file_exists($config['application_config']['logo_partenaire']['url_fichier'])) {
            throw new \Exception("File CSV doesn't exists");            
        }
        $this->filecsv = $config['application_config']['logo_partenaire']['url_fichier'];

        if(!array_key_exists('carac_separateur', $config['application_config']['logo_partenaire'])) {
            throw new \Exception("We must to specify carac_separateur parameter");            
        }
        $this->carac_separateur = $config['application_config']['logo_partenaire']['carac_separateur'];

        if(!array_key_exists('line_depart', $config['application_config']['logo_partenaire'])) {
            throw new \Exception("We must to specify line_depart parameter");            
        }
        $this->line_depart = $config['application_config']['logo_partenaire']['line_depart'];

        if(!array_key_exists('url_archive', $config['application_config']['logo_partenaire'])) {
            throw new \Exception("Archive directory doesn't exists");            
        }
        $this->archive_url = $config['application_config']['logo_partenaire']['url_archive'].DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR.date('Y-m').DIRECTORY_SEPARATOR.date('Y-m-d-H-i-s').'_';
        @mkdir($this->archive_url, 0777, true);

        $this->erreur_url = $config['application_config']['logo_partenaire']['url_erreur'].DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR.date('Y-m').DIRECTORY_SEPARATOR.date('Y-m-d-H-i-s').'_';
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
            $tabSavePartner = array();
            $tabSaveCamping = array();
            $saveLogoUrlId = array();
            $row = 1;
            $dao = $this->serviceloc->get('RepriseBatch\Model\PartnerTable');
            $dao2 = $this->serviceloc->get('RepriseBatch\Model\CampingTable');
                        
            // parcours du fichier ligne par ligne
            if (($handle = fopen($file, "r")) !== FALSE) {
                if(is_int($this->line_depart) && $this->line_depart != 0) {
                    while($this->line_depart != 0) {
                        fgets($handle, 4096);
                        $this->line_depart--;
                    }
                }
                while (($data = fgetcsv($handle, 1000, $this->carac_separateur)) !== FALSE) {
                    // recherche du partenaire
                    if(! array_key_exists($data[3], $tabSavePartner)) {
                        // recherche du partenaire si celui ci n est pas dans le tableau de sauvegarde
                        $temp = $dao->getByLabel($data[3]);
                        if(is_object($temp)){
                            // suppression des lignes de la table logo si le partenaire n'etait pas connu
                            $this->listeParnerLogoInfo($temp);
                            $tabSavePartner[$data[3]] = $temp;
                        } else {
                            $tabSavePartner[$data[3]] = null;
                        }
                    }

                    
                    // recherche du camping
                    if($tabSavePartner[$data[3]] != null) {
                        if(!array_key_exists($data[0], $tabSaveCamping)) {
                            $temp2 = $dao2->getByRef($data[0]);
                            $tabSaveCamping[$data[0]] = null;
                            if($temp2->count() != 0) {
                                $is_exist = true;
                                $obj = $temp2->current();
                                $tabSaveCamping[$data[0]] = $obj;
                            }
                        }

                        // si nous avons un parter et un camping, debut du traitement
                        if($tabSaveCamping[$data[0]] != null) {
                            $myParnterId = $tabSavePartner[$data[3]]->id;
                            $myCampingId = $tabSaveCamping[$data[0]]->id;
                           
                            // on va parcourir la liste des logos
                            foreach ($this->listLogoPartner[$myParnterId] as $idLogo => $listCamping) {
                                if(array_key_exists($myCampingId , $listCamping)) {
                                    $passe = false;
                                    if(! property_exists($listCamping[$myCampingId], "data") || ! is_array($listCamping[$myCampingId]->data) || !array_key_exists('listeUrl', $listCamping[$myCampingId]->data)) {
                                        $listCamping[$myCampingId]->data = array("listeUrl" => array());
                                    } 
                                    if(array_key_exists(strtolower($data[1]), $listCamping[$myCampingId]->data['listeUrl'])) {
                                        if($listCamping[$myCampingId]->data['listeUrl'][strtolower($data[1])]["lien"]!= $data[2]) {
                                            $this->listLogoPartner[$myParnterId][$idLogo][$myCampingId]->data['listeUrl'][strtolower($data[1])]["lien"] = utf8_encode($data[2]);
                                        }
                                    } else {
                                        if(! array_key_exists($data[1], $listCamping[$myCampingId]->data['listeUrl'])) {
                                            $this->listLogoPartner[$myParnterId][$idLogo][$myCampingId]->data['listeUrl'][strtolower($data[1])] = array();
                                        }
                                        $this->listLogoPartner[$myParnterId][$idLogo][$myCampingId]->data['listeUrl'][strtolower($data[1])]["lien"] = utf8_encode($data[2]);
                                    }
                                    if($listCamping[$myCampingId]->id_logo_url != "" && $listCamping[$myCampingId]->id_logo_url != null){
                                        $saveLogoUrlId[] =  $listCamping[$myCampingId]->id_logo_url.'_'.$myCampingId;
                                    }
                                } else { // nous allons devoir creer un logo_url pour ce camping
                                    $this->listLogoPartner[$myParnterId][$idLogo][$myCampingId] = (object)array(
                                        "id_logo_url" => "",
                                        "url_default" => "",
                                        "data" => array("listeUrl" => array(strtolower($data[1]) => array("lien" => utf8_encode($data[2])))),
                                        "blacklist" => "",
                                        "camping_id" => $myCampingId,
                                    );
                                }
                            }
                        }
                    }
                }
                fclose($handle);
            }
            foreach ($this->listLogoPartner as $partnerId => $listLogo) {
                foreach ($listLogo as $idLogo => $listCamping) {
                    foreach ($listCamping as $idCamping => $objectLogoUrl) {
                        if($objectLogoUrl->id_logo_url == "" || $objectLogoUrl->id_logo_url == null) {// creation de 
                            $dao->insertLogo($idLogo, $objectLogoUrl->camping_id, $objectLogoUrl->url_default, json_encode($objectLogoUrl->data));
                        } elseif (in_array($objectLogoUrl->id_logo_url.'_'.$objectLogoUrl->camping_id, $saveLogoUrlId)) { // update
                            $dao->updateLogo($idLogo, $objectLogoUrl->camping_id, $objectLogoUrl->url_default, json_encode($objectLogoUrl->data));
                        } else { // suprpession
                            $dao->deleteLogo($idLogo, $objectLogoUrl->camping_id); 
                        }
                    }
                }
            }
        } catch(\Exception $e) {
            if (!copy($file, $this->erreur_url.'/'.basename($file))) {
                throw new \Exception("La copie du fichier '$file' a échoué...Hors ce fichier est en erreur");
            }
        }
    }

    /**
    * function which load all logo aldready in database for this partner
    * @param Partner $partner partner object
    */
    private function listeParnerLogoInfo(Partner $partner) {
        $this->listLogoPartner[$partner->id] = array();
        try {
            $dao = $this->serviceloc->get('RepriseBatch\Model\PartnerTable');
            $tmp = $dao->getLogoWithUrl($partner);
            if(is_object($tmp)) {
                foreach ($tmp as $key => $value) {
                    $idLogo = $value->id;
                    if(!array_key_exists($idLogo, $this->listLogoPartner[$partner->id])){
                        $this->listLogoPartner[$partner->id][$idLogo] = array();
                    }
                    if($value->camping_id != null && $value->camping_id != "") {
                        if(! array_key_exists($value->camping_id, $this->listLogoPartner[$partner->id][$idLogo])) {
                            $this->listLogoPartner[$partner->id][$idLogo][$value->camping_id] = array();
                        }

                        unset($value->id);
                        $temp = json_decode($value->data, true);
                        if(json_last_error() == JSON_ERROR_NONE) {
                            $value->data = $temp;
                            $this->listLogoPartner[$partner->id][$idLogo][$value->camping_id] = $value;
                        }
                    }
                }
            }
        } catch(\Exception $e) {}
    }
}
