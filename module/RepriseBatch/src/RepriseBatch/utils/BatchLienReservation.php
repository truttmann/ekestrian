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


class BatchLienReservation extends BatchCSV {
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

        if(!array_key_exists('application_config', $config) || !array_key_exists('lien_reservation', $config['application_config']) || !array_key_exists('url_fichier', $config['application_config']['lien_reservation']) || !file_exists($config['application_config']['lien_reservation']['url_fichier'])) {
            throw new \Exception("File CSV doesn't exists");            
        }
        $this->filecsv = $config['application_config']['lien_reservation']['url_fichier'];

        if(!array_key_exists('carac_separateur', $config['application_config']['lien_reservation'])) {
            throw new \Exception("We must to specify carac_separateur parameter");            
        }
        $this->carac_separateur = $config['application_config']['lien_reservation']['carac_separateur'];

        if(!array_key_exists('line_depart', $config['application_config']['lien_reservation'])) {
            throw new \Exception("We must to specify line_depart parameter");            
        }
        $this->line_depart = $config['application_config']['lien_reservation']['line_depart'];

        if(!array_key_exists('url_archive', $config['application_config']['lien_reservation'])) {
            throw new \Exception("Archive directory doesn't exists");            
        }
        $this->archive_url = $config['application_config']['lien_reservation']['url_archive'].DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR.date('Y-m').DIRECTORY_SEPARATOR.date('Y-m-d-H-i-s').'_';
        @mkdir($this->archive_url, 0777, true);

        $this->erreur_url = $config['application_config']['lien_reservation']['url_erreur'].DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR.date('Y-m').DIRECTORY_SEPARATOR.date('Y-m-d-H-i-s').'_';
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
            $saveContract = array();
            $tabDejaFait = array();
            $row = 1;
            $dao = $this->serviceloc->get('RepriseBatch\Model\PartnerTable');
            $dao2 = $this->serviceloc->get('RepriseBatch\Model\CampingTable');
            $dao3 = $this->serviceloc->get('RepriseBatch\Model\ContractTable');
                        
            // parcours du fichier ligne par ligne
            if (($handle = fopen($file, "r")) !== FALSE) {
                
                // nous nous mettons a la ligne de depart definie dans le fichier de configuration
                if(is_int($this->line_depart) && $this->line_depart != 0) {
                    while($this->line_depart != 0) {
                        fgets($handle, 4096);
                        $this->line_depart--;
                    }
                }

                /*
                    ON VA ENREGISTRER L ENSEMBLE DANS LA TABLE CONTRACT
                    pour ce faire, nous allons appliquer les regles suivantes:
                        - si le partenaire n'a pas de contract, nous loggons et nous passons au lien suivant
                        - si le partenaire a un contract, nous allons verifier si se contract est lie au camping
                            - si oui, verification du lien + update si necessaire
                            - si non, creation du contract
                */
                while (($data = fgetcsv($handle, 1000, $this->carac_separateur)) !== FALSE) {
                    // recuperation du partenaire si ce n est deja fait
                    if(! array_key_exists($data[3], $tabSavePartner)) {
                        // recherche du partenaire si celui ci n est pas dans le tableau de sauvegarde
                        $temp = $dao->getByLabel($data[3]);
                        if(is_object($temp)){
                            // remplissage du la variable $this->listLienReservationPartner, avec toutes les informations concernant le contract partenaire et les contract camping
                            $this->listeParnerLienReservationInfo($temp);
                            $tabSavePartner[$data[3]] = $temp;
                        } else {
                            $tabSavePartner[$data[3]] = null;
                        }
                    }
                    
                    // si nous avons bien trouve le partner
                    if($tabSavePartner[$data[3]] != null) {
                        // nous verifions les information sur le camping
                        if(!array_key_exists($data[0], $tabSaveCamping)) {
                            $temp2 = $dao2->getByRef($data[0]);
                            $tabSaveCamping[$data[0]] = null;
                            if($temp2->count() != 0) {
                                $obj = $temp2->current();
                                $tabSaveCamping[$data[0]] = $obj;
                            }
                        }

                        // si nous avons bien trouve le camping
                        if($tabSaveCamping[$data[0]] != null) {

                            // on va parcoursir tous les contracts reservation du partenaire
                            foreach ($this->listLienReservationPartner[$tabSavePartner[$data[3]]->id] as $contractreservPartenaireId => $value) {
                                $passe = false;
                                // recherche du contract camping de ce camping et partner
                                if(array_key_exists($tabSaveCamping[$data[0]]->id, $value)) {
                                    // si son data est remplit
                                    if($value[$tabSaveCamping[$data[0]]->id]->data != null && $value[$tabSaveCamping[$data[0]]->id]->data != "" && is_array($value[$tabSaveCamping[$data[0]]->id]->data)) {
                                        foreach ($value[$tabSaveCamping[$data[0]]->id]->data as $langue => $url) {
                                            
                                            // verification que la langue existe pour ce camping-partenaire
                                            if($langue == strtolower($data[1])) {
                                                $passe = true;

                                                // verification qu'il y a eut un chagement d'url
                                                if($url != $data[2]) {
                                                    $this->listLienReservationPartner[$tabSavePartner[$data[3]]->id][$contractreservPartenaireId][$tabSaveCamping[$data[0]]->id]->data[$langue] = $data[2];
                                                }
                                            }
                                        }
                                    }

                                    // le contract camping existe bien mais ne possede pas de data remplit, ou de data avec cette langue
                                    if($passe == false) {
                                        if(! is_array($this->listLienReservationPartner[$tabSavePartner[$data[3]]->id][$contractreservPartenaireId][$tabSaveCamping[$data[0]]->id]->data)) {
                                            $this->listLienReservationPartner[$tabSavePartner[$data[3]]->id][$contractreservPartenaireId][$tabSaveCamping[$data[0]]->id]->data = array();
                                        }
                                        $this->listLienReservationPartner[$tabSavePartner[$data[3]]->id][$contractreservPartenaireId][$tabSaveCamping[$data[0]]->id]->data[strtolower($data[1])] = $data[2];
                                    }
                                

                                    // sauvegarde du contract camping modifie
                                    $saveContract[] = $this->listLienReservationPartner[$tabSavePartner[$data[3]]->id][$contractreservPartenaireId][$tabSaveCamping[$data[0]]->id]->id;

                                } else { // si nous ne l'avons pas trouve, nous creons ce contract reservation camping 
                                    $cls = new Contract();
                                    $cls->camping_id = $tabSaveCamping[$data[0]]->id;
                                    $cls->package_products_id = PACKAGE_PRODUCT_RESERVATION;
                                    $cls->parent_id = $contractreservPartenaireId;
                                    $cls->date_start = $this->listContractPartner[$contractreservPartenaireId]->date_start;
                                    $cls->date_end = $this->listContractPartner[$contractreservPartenaireId]->date_end;
                                    $cls->data = array(
                                        strtolower($data[1]) => $data[2]
                                    );
                                    
                                    // mise a jour de la liste de liens de reservation
                                    if(! array_key_exists($contractreservPartenaireId, $this->listLienReservationPartner[$tabSavePartner[$data[3]]->id])) {
                                        $this->listLienReservationPartner[$tabSavePartner[$data[3]]->id][$contractreservPartenaireId] = array();
                                    }
                                    if(! array_key_exists($tabSaveCamping[$data[0]]->id, $this->listLienReservationPartner[$tabSavePartner[$data[3]]->id][$contractreservPartenaireId])) {
                                        $this->listLienReservationPartner[$tabSavePartner[$data[3]]->id][$contractreservPartenaireId][$tabSaveCamping[$data[0]]->id] = $cls;
                                    }
                                }
                            }
                        }
                    }
                }
                fclose($handle);
            }
            // enregistrmement des contracts
            foreach ($this->listLienReservationPartner as $partnerId => $listcontractPartnerId) {
                foreach ($listcontractPartnerId as $contractPartnerId => $listcontractCamping) {
                    foreach ($listcontractCamping as $campingId => $contractCamping) {
                        $contractCamping->data = json_encode($contractCamping->data);
                        // execution des creation de contract camping
                        if($contractCamping->id == null) {
                            $contractCamping->created_at = date('Y-m-d');
                            $dao3->save($contractCamping);
                        } elseif (in_array($contractCamping->id, $saveContract)) { // execution des update
                            $dao3->save($contractCamping);
                        } else {
                            $dao3->delete($contractCamping);
                        }                
                    }
                }            
            }

            foreach ($this->doublonContract as $key => $value) {
                $dao3->delete($value);
            }
        } catch(\Exception $e) {
            if (!copy($file, $this->erreur_url.'/'.basename($file))) {
                throw new \Exception("La copie du fichier '$file' a échoué...Hors ce fichier est en erreur");
            }
        }
    }

    /**
    * function which load all reservations' link aldready in database for this partner
    * @param Partner $partner partner object
    */
    private function listeParnerLienReservationInfo(Partner $partner) {
        $this->listLienReservationPartner = array($partner->id => array());
        try {            
            $dao3 = $this->serviceloc->get('RepriseBatch\Model\ContractTable');

            // recherche du contrat de ce partenaire
            $res = $dao3->getNonPassedReservationByPartner($partner->id);
            if(is_object($res)) {
                foreach ($res as $key => $value) {
                    // on enregistre le numero du contract
                    if(!array_key_exists($value->id, $this->listLienReservationPartner[$partner->id])) {
                        $this->listLienReservationPartner[$partner->id][$value->id] = array();
                    }
                    
                    $this->listContractPartner[$value->id] = $value;

                    // pour chaque contract nous allons rechercher les contract camping associes
                    $res2 = $dao3->getContractByParent($value->id);
                    if(is_object($res2)) {
                        foreach ($res2 as $k => $v) {
                            //Si un doublon de contract existe, nous l'enregistrons pour le supprimer a la fin du batch
                            if(array_key_exists($v->camping_id, $this->listLienReservationPartner[$partner->id][$value->id])) {
                                $this->doublonContract[] = $v;
                            } else{
                                // on enregistre le contract 
                                $this->listLienReservationPartner[$partner->id][$value->id][$v->camping_id] = $v;
                            
                                // on decode la data
                                $temp = json_decode($v->data, true);
                                if(json_last_error() == JSON_ERROR_NONE) {
                                    $this->listLienReservationPartner[$partner->id][$value->id][$v->camping_id]->data = $temp;
                                } else {
                                    $this->listLienReservationPartner[$partner->id][$value->id][$v->camping_id]->data = "";
                                }
                            }
                        }
                    }
                }
            }
        } catch(\Exception $e) {}
    }
}
