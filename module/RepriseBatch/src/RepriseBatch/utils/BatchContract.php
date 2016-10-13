<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RepriseBatch\utils;

use RepriseBatch\Controller\CampingController;
use RepriseBatch\Model\Pays;
use RepriseBatch\Model\User;
use RepriseBatch\Model\Contract;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\ServiceManager\ServiceManager;

// REFAIRE TOUS PAR RAPPORT A LA CLASSE BATCH


class BatchContract {

    private $list_code_langue = array('de', 'es', 'uk', 'nl');

    private $idProductUrl;
    private $config;
    private $serviceloc;

    /**
     * @var \RepriseBatch\Model\CampingTable
     */
    private $dao;
    private $daoContract;
    private $campingi18nInfo = array();
    private $campingi18nInfoNew = array();
    private $hasVideo = false;
    private $nbr_phot_to_activate = null;
    private $listThematicCamping = array();
    private $hasContractFavoris = false;
    private $hasContractSelection = false;

    /**
    * constructeur
    * @param  ServiceManager $serviceloc manager de service
    * @throws Exception
    */
    public function __construct(ServiceManager $serviceloc) {
        ini_set('memory_limit', '256M');
        
        $this->serviceloc = $serviceloc;
        $config = $serviceloc->get('Config');

        if(!array_key_exists('application_config', $config) || !array_key_exists('contract', $config['application_config']) || !array_key_exists('id_product_url', $config['application_config']['contract'])) {
            throw new \Exception("File xml doesn't exists");
        }
        $this->idProductUrl = $config['application_config']['contract']['id_product_url'];

        $this->dao = $this->serviceloc->get('RepriseBatch\Model\CampingTable');
        $this->daoContract = $this->serviceloc->get('RepriseBatch\Model\ContractTable');
    }

    /**
    * function qui va instancier le traitement des fichiers xml
    * elle va verifier que le chemin fournit est un fichier, si c"est un repertoire, elle va tourner en boucle sur celui-ci pour prendre chacuns des fichiers par ordre de DATE de modification ASC,
    * et elle archive les fichiers
    */
    public function init() {
        $this->traitementTousContract();
    }

    /**
    * function which call another function for each camping find in database
    */
    private function traitementTousContract() {
        // traitement des logos / activation et desactivation des logo partenaire
        $this->activeLogo();

        // traitement des bons plan
        $this->traitementContratBonPLan();

        // traitement de tous les contracts concernant les campings
        $listCamping = $this->dao->fetchAll();
        if(is_object($listCamping)) {
            foreach ($listCamping as $key => $value) {
                $this->campingi18nInfo = array();
                $this->campingi18nInfoNew = array();
                $this->hasVideo = false;
                $this->hasContractFavoris = false;
                $this->hasContractSelection = false;
                $this->nbr_phot_to_activate = null;
                $this->traitementContract($value->id);
            }
        } else {
            throw new \Exception("Unable to list camping");
        }

        // Mise a jour des priority des campings
        $this->dao->updatePriority();

    }

    /**
    * function which all camping data with contratc data
    * @param int $campingId camping identifier
    */
    public function traitementContract($campingId) {
        // recuperation de tous les information, par langue, de ce camping
        $this->getCampingInfo($campingId);

        // traitement des contract de facon specifique
        $this->traitementLogo($campingId);

        // recuperation de tous les contracts concernant ce camping
        $res = $this->daoContract->getActiveContractCamping($campingId);
        if(is_object($res)) {
            foreach ($res as $key => $contract) {
                switch ($contract->package_products_id) {
                    case PACKAGE_PRODUCT_COUP_COEUR:
                        $this->traitementContratCoupDeCoeur($contract, $campingId);
                        break;
                    case PACKAGE_PRODUCT_SELECTION:
                        $this->traitementContratNotreSelection($contract, $campingId);
                        break;
                    case PACKAGE_PRODUCT_RESERVATION:
                        $this->traitementContratReservation($contract, $campingId);
                        break;
                    case PACKAGE_PRODUCT_LIEN_INTERNET:
                        $this->traitementContratLienInternet($contract, $campingId);
                        break;
                    case PACKAGE_PRODUCT_VIDEO:
                        $this->traitementContratVideo($contract, $campingId);
                        break;
                    case PACKAGE_THEMATIQUE_LOISIRS_FAVORIS:
                    case PACKAGE_THEMATIQUE_SITES_TOURISTIQUE:
                        $this->traitementContratThematique($contract, $campingId);
                        break;
                    default:
                        break;
                }
            }
        }

        // enregistrement des informations de ce camping
        $this->saveCampingUpdates($campingId);

        // on va retirer les thematic non ajoute
        $this->dao->removeCampingThematicContractLink($campingId, $this->listThematicCamping);

        // verification updates camping
        $this->checkUpdateForCamping($campingId);

        // traitement des photos
        $this->activePhoto($campingId);

        // traitement des videos
        $this->activeVideo($campingId);
    }

    /**
    * function which save all update for this campings
    * @param  int $campingId camping identifier
    * @return void
    */
    private function checkUpdateForCamping($campingId) {
        $obj = $this->dao->getCamping($campingId);
        $passe = false;
        if(($obj->_is_favorite == 1 && $this->hasContractFavoris === false) || $obj->_is_favorite != 1 && $this->hasContractFavoris === true) {
            $obj->_is_favorite = ($this->hasContractFavoris === true) ? 1 : 0;
            $passe = true;
        }
        if(($obj->_is_our_selection == 1 && $this->hasContractSelection === false) || $obj->_is_our_selection != 1 && $this->hasContractSelection === true) {
            $obj->_is_our_selection = ($this->hasContractSelection === true) ? 1 : 0;
            $passe = true;
        }
        if($passe == true) {
            $this->dao->save($obj);
        }
    }

    /**
    * function which save all update for this campings i18n
    * @param  int $campingId camping identifier
    * @return void
    */
    private function saveCampingUpdates($campingId) {
        $updateNull = false;
        if(! empty($this->campingi18nInfoNew)) {
            foreach ($this->campingi18nInfo as $langue) { // recherche des modification
                if(in_array($langue, array_merge(array('fr'), $this->list_code_langue))) {
                    if(in_array(strtolower($langue), $this->campingi18nInfoNew)) { // modification

                        /* remplissage des la description meta */
                        if(empty($this->campingi18nInfoNew[$langue]->_long_description)) {
                            $this->loadMetaDescription($campingId, $langue);
                        }

                        /* formatage de l'objet pour enregistrement bdd */
                        $this->transformeForBdd($langue);

                        $this->dao->updatei18nInformation($campingId, $this->campingi18nInfoNew[$langue]);
                        unset($this->campingi18nInfoNew[$langue]);
                    } else { // suppression
                        $this->dao->deletei18nInformation($campingId, strtolower($langue));
                    }
                }
            }
            foreach ($this->campingi18nInfoNew as $langue => $object) {
                if(in_array($langue, array_merge(array('fr'), $this->list_code_langue))) {

                    /* remplissage des la description meta */
                    if(empty($this->campingi18nInfoNew[$langue]->_long_description)) {
                        $this->loadMetaDescription($campingId, $langue);
                    }

                    /* formatage de l'objet pour enregistrement bdd */
                    $this->transformeForBdd($langue);

                    $this->dao->inserti18nInformation($campingId, $this->campingi18nInfoNew[$langue]);
                }
            }
        } elseif(! empty($this->campingi18nInfo)) {// si nous avions des données pour ce camping, nous les supprimons
            $this->dao->deletei18nInformation($campingId, null);

            /* remplissage des description dynamiques et des meta description */
            $updateNull = true;
        } else {
            /* remplissage des description dynamiques et des meta description */
            $updateNull  = true;
        }

        /* remplissage des description dynamiques et des meta description */
        if($updateNull == true) {
            foreach (array_merge($this->list_code_langue, array('fr')) as $langue) {
                $this->campingi18nInfoNew = array();
                $this->campingi18nInfoNew[$langue] = (object)array(
                    "id" => $campingId,
                    "code" => $langue,
                    "_short_description" => "",
                    "_long_description" => "",
                    "_meta_description" => "",
                    "_url_reservation" => "",
                    "_urls_data" => "",
                    "_logos_data" => "",
                    "_url_favorite" => "",
                    "_url_selection" => "",
                );
                $this->loadMetaDescription($campingId, $langue);
                $this->dao->inserti18nInformation($campingId, $this->campingi18nInfoNew[$langue]);
            }
        }
    }

    /**
     * function to make an object reading for bdd
     * @param  string $langue
     * @return void
     */
    private function transformeForBdd($langue){
        $this->campingi18nInfoNew[$langue]->_url_reservation = json_encode($this->campingi18nInfoNew[$langue]->_url_reservation);
        $this->campingi18nInfoNew[$langue]->_urls_data = json_encode($this->campingi18nInfoNew[$langue]->_urls_data);
        $this->campingi18nInfoNew[$langue]->_logos_data = json_encode($this->campingi18nInfoNew[$langue]->_logos_data);
        $this->campingi18nInfoNew[$langue]->_url_favorite = json_encode($this->campingi18nInfoNew[$langue]->_url_favorite);
        $this->campingi18nInfoNew[$langue]->_url_selection = json_encode($this->campingi18nInfoNew[$langue]->_url_selection);
    }

    /**
     * function which load camping meta description
     * @param  int    $campingId
     * @param  string $langue
     * @return void
     */
    private function loadMetaDescription($campingId, $langue) {
        // c'est tres tres moche !!!
        $campingTable = $this->serviceloc->get('Application\Camping\Model\CampingTable');
        $tempCpt = $campingTable->fetchOne($campingId);

        $tmpCtl = new CampingController();
        $tmpCtl->setServiceLocator($this->serviceloc);
        $tmpCtl->init();
        $tmpCpt2 = $tmpCtl->generateCampingDescription($tempCpt, $langue);
        $this->campingi18nInfoNew[$langue]->_long_description = $tmpCpt2->longDescription;
        $this->campingi18nInfoNew[$langue]->_meta_description = $tmpCpt2->metaDescription;
    }

    /**
    * function which get camping information
    * @param  int  $campingId camping identifier
    * @return void
    */
    private function getCampingInfo($campingId) {
        $res = $this->dao->getCampingI18nInfo($campingId);

        if(is_object($res)) {
            foreach ($res as $key => $value) {
                $this->campingi18nInfo[] = $value->code;
            }
        }
    }

    /**
    * function which process Contrat Bon PLan
    * @param  int  $bon_plan_id optionnel 
    * @return void
    */
    public function traitementContratBonPLan($bon_plan_id = null) {
        /*
            le but est d'appliquer les regles de gestion suivantes
            - pour un contract bon plan campng => un seul bon plan valide a active
            - pour un contract bon plan partner => un seul bon plan valide par departement a active
            - les bon plan partenaire sont forcement lie a un departement
        */
        $listCamp = array();
        $listPart = array();

        $dao2 = $this->serviceloc->get('RepriseBatch\Model\GoodPlanTable');
        $dao3 = $this->serviceloc->get('RepriseBatch\Model\ContractTable');

        // liste de tous les bon plan partenaire
        $temp = $dao2->getActiveGoodPlanPartenaire();
        $array_bon_plan_dept = array();
        $array_bon_plan_actif = array();
        $array_list_bon_plan_to_active = array();
        $contract_courant = "";
        if(is_object($temp)) {
            foreach ($temp as $key => $value) {
                // si nous changeons de contract, nour reinitialisons certaines variables
                if($contract_courant != $value['contract_id']) {
                    $array_bon_plan_dept = array();
                    $contract_courant = $value['contract_id'];
                }
                // verification qu'il n exitse qu'un seul bon plan pour ce partenaire et ce departement
                if(! array_key_exists($value['partner_id']."_".$value['department_id'], $array_bon_plan_dept)) {

                    // verification que le bon plan n'est pas deja actif
                    if(! array_key_exists($value['partner_id']."_".$value['id'], $array_bon_plan_actif)) {
                        $array_bon_plan_dept[$value['partner_id']."_".$value['department_id']] = "";
                        $array_bon_plan_actif[$value['partner_id']."_".$value['id']] = "";
                        $array_list_bon_plan_to_active[] = $value['id'];
                    }
                }
            }
        }

        // liste de tous les bon plan camping
        $temp = $dao2->getActiveGoodPlanCamping();
        $array_bon_plan_cpt = array();
        $array_bon_plan_actif = array();
        $contract_courant = "";
        if(is_object($temp)) {
            foreach ($temp as $key => $value) {
                // verification qu'il n exitse qu'un seul bon plan pour ce camping et ce contract
                if(! array_key_exists($value['camping_id']."_".$value['contract_id'], $array_bon_plan_cpt)) {

                    // verification que le bon plan n'est pas deja actif
                    if(! array_key_exists($value['camping_id']."_".$value['id'], $array_bon_plan_actif)) {
                        $array_bon_plan_cpt[$value['camping_id']."_".$value['contract_id']] = "";
                        $array_bon_plan_actif[$value['camping_id']."_".$value['id']] = "";
                        $array_list_bon_plan_to_active[] = $value['id'];
                    }
                }
            }
        }

        // activation du bon plan dans le cas ou l'on a saisi un bon plan
        if($bon_plan_id != null && is_numeric($bon_plan_id)) {
            if(in_array($bon_plan_id, $array_list_bon_plan_to_active)) {
                // Activation du bon plan
                $dao2->activeGoodPlan($bon_plan_id);    
            }   
        } else {
            // Activation de tous les bon plan des partenaires ou camping trouve
            $dao2->activeDesactiveAll($array_list_bon_plan_to_active);
        }
    }

    /**
    * function which process Contrat Lien Internet
    * @param  Contract $contract  object contract
    * @param  int      $campingId camping identifier
    * @return void
    */
    private function traitementContratLienInternet(Contract $contract, $campingId) {
        //on enregistre les camping qui on des contract internet, pouractiver leurs photo

        $list_type_contract_internet = unserialize(TYPE_CONTRACT_LIEN_INTERNET);
        $this->nbr_phot_to_activate = (!empty($contract->type) && array_key_exists($contract->type, $list_type_contract_internet) && $list_type_contract_internet[$contract->type] > $this->nbr_phot_to_activate)?$list_type_contract_internet[$contract->type]:$this->nbr_phot_to_activate;

        /* recuperation des informations du partenaire */
        $part = null;
        if($contract->parent_id != null) {
            try {
                $objC = $this->daoContract->getById($contract->parent_id);
                if(is_object($objC) && $objC->partner_id != NULL){
                    $objPart = $this->serviceloc->get('RepriseBatch\Model\PartnerTable')->getById($objC->partner_id);
                    if(is_object($objPart)) {
                        $part = $objPart;
                    }
                }
            } catch(\Exception $e){}
        }

        //traitement du contract
        $data = json_decode($contract->data, true);
        $saveTabCodeLangue = array();
        if(json_last_error() == JSON_ERROR_NONE && is_array($data)) {
            foreach ($data as $language => $arrayContractUrl) {
                if(array_key_exists(strtolower($language), $this->campingi18nInfoNew)) {
                    if(! is_array($this->campingi18nInfoNew[strtolower($language)]->_urls_data)){
                        $this->campingi18nInfoNew[strtolower($language)]->_urls_data = array();
                    }
                    $this->campingi18nInfoNew[strtolower($language)]->_urls_data[] = (object)array(
                        "url" => $arrayContractUrl['url'],
                        "thematic_id" => array(),
                        "partner" => ($contract->parent_id != '' && $contract->parent_id != null)?true:false,
                        "id_partner" => ((is_object($part))?$part->reference:null),
                        "lab_partner" => ((is_object($part))?$part->label:null)
                    );
                    $this->campingi18nInfoNew[strtolower($language)]->_long_description = ((array_key_exists("description", $arrayContractUrl))?$arrayContractUrl['description']:"");
                    $this->campingi18nInfoNew[strtolower($language)]->_short_description = ((array_key_exists("description", $arrayContractUrl))?((strlen($arrayContractUrl['description'])>200)?(substr($arrayContractUrl['description'],0,200).'...'):$arrayContractUrl['description']):"");
                } else {
                    $this->campingi18nInfoNew[strtolower($language)] = (object)array(
                        "id" => $campingId ,
                        "code" => strtolower($language) ,
                        "_short_description" => ((array_key_exists("description", $arrayContractUrl))?((strlen($arrayContractUrl['description'])>200)?(substr($arrayContractUrl['description'],0,200).'...'):$arrayContractUrl['description']):""),
                        "_long_description" => ((array_key_exists("description", $arrayContractUrl))?$arrayContractUrl['description'] : ''),
                        "_url_reservation" => array(),
                        "_urls_data" => array(
                            (object)array(
                                "url" => $arrayContractUrl['url'],
                                "thematic_id" => array(),
                                "partner" => ($contract->parent_id != '' && $contract->parent_id != null)?true:false,
                                "id_partner" => ((is_object($part))?$part->reference:null),
                                "lab_partner" => ((is_object($part))?$part->label:null)
                            )
                        ),
                        "_logos_data" => array(),
                        "_url_favorite" => array(),
                        "_url_selection" => array(),
                    );
                }
                if(trim($arrayContractUrl['url']) != "" && $arrayContractUrl['url'] != null) {
                    $saveTabCodeLangue[] = strtolower($language);
                }
            }
        }

        // nous allons prendre pour reference l'url du contrat en fr, et le dupliquer dans les autres langues
        // si l'url est vide
        if(array_key_exists("fr", $this->campingi18nInfoNew) && property_exists($this->campingi18nInfoNew["fr"], "_urls_data") && count($this->campingi18nInfoNew["fr"]->_urls_data) != 0) {
            $_url_con = $this->campingi18nInfoNew["fr"]->_urls_data[count($this->campingi18nInfoNew["fr"]->_urls_data) - 1]->url;
            foreach ($this->list_code_langue as $code_langue) {
                if(! in_array($code_langue, $saveTabCodeLangue)){
                    if(array_key_exists(strtolower($code_langue), $this->campingi18nInfoNew)) {
                        if(! is_array($this->campingi18nInfoNew[strtolower($code_langue)]->_urls_data) || count($this->campingi18nInfoNew[strtolower($code_langue)]->_urls_data) == 0){
                            $this->campingi18nInfoNew[strtolower($code_langue)]->_urls_data = array(
                                (object)array(
                                   "url" => $_url_con,
                                   "thematic_id" => array(),
                                   "partner" => ($contract->parent_id != '' && $contract->parent_id != null)?true:false,
                                    "id_partner" => ((is_object($part))?$part->reference:null),
                                    "lab_partner" => ((is_object($part))?$part->label:null)
                               )
                           );
                        } else {
                            $this->campingi18nInfoNew[strtolower($code_langue)]->_urls_data[] = (object)array(
                               "url" => $_url_con,
                               "thematic_id" => array(),
                               "partner" => ($contract->parent_id != '' && $contract->parent_id != null)?true:false,
                                "id_partner" => ((is_object($part))?$part->reference:null),
                                "lab_partner" => ((is_object($part))?$part->label:null)
                           );
                        }
                    } else {
                        $this->campingi18nInfoNew[strtolower($code_langue)] = (object)array(
                            "id" => $campingId ,
                            "code" => strtolower($code_langue) ,
                            "_short_description" => "",
                            "_long_description" => "",
                            "_url_reservation" => array(),
                            "_urls_data" => array(
                                (object)array(
                                    "url" => $_url_con,
                                    "thematic_id" => array(),
                                    "partner" => ($contract->parent_id != '' && $contract->parent_id != null)?true:false,
                                    "id_partner" => ((is_object($part))?$part->reference:null),
                                    "lab_partner" => ((is_object($part))?$part->label:null)
                                )
                            ),
                            "_logos_data" => array(),
                            "_url_favorite" => array(),
                            "_url_selection" => array(),
                        );
                    }
                }
            }
        }
    }

    /**
    * function which process Contrat Thematique
    * @param  Contract $contract  object contract
    * @param  int      $campingId camping identifier
    * @return void
    */
    private function traitementContratThematique(Contract $contract, $campingId) {
        // traitement du contract - enregristrement des URL
        // recuperation du data de la table contract_thematic,
        $res = $this->daoContract->getContractThematicFromContract($contract);
        foreach ($res as $key => $value) {
            $data = json_decode($value['data']);
            $saveTabCodeLangue = array();
            if($data && json_last_error() == JSON_ERROR_NONE) {
                foreach ($data as $language => $objectContractUrl) {
                    if(array_key_exists(strtolower($language), $this->campingi18nInfoNew)) {
                        if(! is_array($this->campingi18nInfoNew[strtolower($language)]->_urls_data)){
                            $this->campingi18nInfoNew[strtolower($language)]->_urls_data = array();
                        }
                        $this->campingi18nInfoNew[strtolower($language)]->_urls_data[] = (object)array(
                            "url" => $objectContractUrl->url,
                            "thematic_id" => array($value['thematic_id']),
                            "partner" => false
                        );
                    } else {
                        $this->campingi18nInfoNew[strtolower($language)] = (object)array(
                            "id" => $campingId ,
                            "code" => strtolower($language) ,
                            "_short_description" => "",
                            "_long_description" => "",
                            "_url_reservation" => array(),
                            "_urls_data" => array(
                                (object)array(
                                    "url" => $objectContractUrl->url,
                                    "thematic_id" => array($value['thematic_id']),
                                    "partner" => false
                                )
                            ),
                            "_logos_data" => array(),
                            "_url_favorite" => array(),
                            "_url_selection" => array(),
                        );
                    }


                    if(trim($objectContractUrl->url) != "" && $objectContractUrl->url != null) {
                        $saveTabCodeLangue[] = strtolower($language);
                    }
                }

                // nous allons prendre pour reference l'url du contrat en fr, et le dupliquer dans les autres langues
                // si l'url est vide
                if(array_key_exists("fr", $this->campingi18nInfoNew) && property_exists($this->campingi18nInfoNew["fr"], "_urls_data") && count($this->campingi18nInfoNew["fr"]->_urls_data) != 0) {
                    $_url_con = $this->campingi18nInfoNew["fr"]->_urls_data[count($this->campingi18nInfoNew["fr"]->_urls_data) - 1]->url;
                    foreach ($this->list_code_langue as $code_langue) {
                        if(! in_array(strtolower($code_langue), $saveTabCodeLangue)){
                            if(array_key_exists(strtolower($code_langue), $this->campingi18nInfoNew)) {
                                if(! is_array($this->campingi18nInfoNew[strtolower($code_langue)]->_urls_data) || count($this->campingi18nInfoNew[strtolower($code_langue)]->_urls_data) == 0){
                                    $this->campingi18nInfoNew[strtolower($code_langue)]->_urls_data = array(
                                        (object)array(
                                           "url" => $_url_con,
                                           "thematic_id" => array($value['thematic_id']),
                                           "partner" => false
                                       )
                                   );
                                } else {
                                    $this->campingi18nInfoNew[strtolower($code_langue)]->_urls_data[] = (object)array(
                                       "url" => $_url_con,
                                       "thematic_id" => array($value['thematic_id']),
                                       "partner" => false
                                   );
                                }
                            } else {
                                $this->campingi18nInfoNew[strtolower($code_langue)] = (object)array(
                                    "id" => $campingId ,
                                    "code" => strtolower($code_langue) ,
                                    "_short_description" => "",
                                    "_long_description" => "",
                                    "_url_reservation" => array(),
                                    "_urls_data" => array(
                                        (object)array(
                                            "url" => $_url_con,
                                            "thematic_id" => array($value['thematic_id']),
                                            "partner" => false
                                        )
                                    ),
                                    "_logos_data" => array(),
                                    "_url_favorite" => array(),
                                    "_url_selection" => array(),
                                );
                            }
                        }
                    }
                }
                // VOIR POUR ACTIVATION DE LA THEMATIQUE POUR CE CAMPING
                /*
                    ajout de la thematique ci elle n'existe pas
                    sinon passage de is_contract = 1
                */
                $this->dao->saveCampingThematicContractLink($campingId, $value['thematic_id']);

                // on sauvegarde la thematic ajouter
                $this->listThematicCamping[] = $value['thematic_id'];
            }
        }
    }

    /**
    * function which process Contrat Coup De Coeur
    * @param  Contract $contract  object contract
    * @param  int      $campingId camping identifier
    * @return void
    */
    private function traitementContratCoupDeCoeur(Contract $contract, $campingId) {
        $this->hasContractFavoris = true;
        $data = json_decode($contract->data);
        if(json_last_error() == JSON_ERROR_NONE) {
            if($data){
                $saveTabCodeLangue = array();
                foreach ($data as $language => $objectContractUrl) {
                    if(array_key_exists(strtolower($language), $this->campingi18nInfoNew)) {
                        if(! is_array($this->campingi18nInfoNew[strtolower($language)]->_url_favorite)){
                            $this->campingi18nInfoNew[strtolower($language)]->_url_favorite = array();
                        }
                        $this->campingi18nInfoNew[strtolower($language)]->_url_favorite[] = $objectContractUrl->url;
                    } else {
                        $this->campingi18nInfoNew[strtolower($language)] = (object)array(
                            "id" => $campingId,
                            "code" => strtolower($language),
                            "_short_description" => "",
                            "_long_description" => "",
                            "_url_reservation" => array(),
                            "_urls_data" => array(),
                            "_logos_data" => array(),
                            "_url_favorite" => array($objectContractUrl->url),
                            "_url_selection" => array(),
                        );
                    }

                    if(trim($objectContractUrl->url) != "" && $objectContractUrl->url != null) {
                        $saveTabCodeLangue[] = strtolower($language);
                    }
                }

                // nous allons prendre pour reference l'url du contrat en fr, et le dupliquer dans les autres langues
                // si l'url est vide
                if(array_key_exists("fr", $this->campingi18nInfoNew) && property_exists($this->campingi18nInfoNew["fr"], "_url_favorite") && count($this->campingi18nInfoNew["fr"]->_url_favorite) != 0) {
                    $_url_con = $this->campingi18nInfoNew["fr"]->_url_favorite[count($this->campingi18nInfoNew["fr"]->_url_favorite) - 1];
                    foreach ($this->list_code_langue as $code_langue) {
                        if(! in_array($code_langue, $saveTabCodeLangue)){
                            if(array_key_exists(strtolower($code_langue), $this->campingi18nInfoNew)) {
                                $this->campingi18nInfoNew[strtolower($code_langue)]->_url_favorite = array($_url_con);
                            } else {
                                $this->campingi18nInfoNew[strtolower($code_langue)] = (object)array(
                                    "id" => $campingId,
                                    "code" => strtolower($code_langue),
                                    "_short_description" => "",
                                    "_long_description" => "",
                                    "_url_reservation" => array(),
                                    "_urls_data" => array(),
                                    "_logos_data" => array(),
                                    "_url_favorite" => array($_url_con),
                                    "_url_selection" => array(),
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
    * function which process Contrat Notre Selection
    * @param  Contract $contract  object contract
    * @param  int  $campingId camping identifier
    * @return void
    */
    private function traitementContratNotreSelection(Contract $contract, $campingId) {
        $this->hasContractSelection = true;
        $data = json_decode($contract->data);
        if(json_last_error() == JSON_ERROR_NONE) {
            $saveTabCodeLangue = array();
            foreach ($data as $language => $objectContractUrl) {
                if(array_key_exists(strtolower($language), $this->campingi18nInfoNew)) {
                    if(! is_array($this->campingi18nInfoNew[strtolower($language)]->_url_selection)){
                        $this->campingi18nInfoNew[strtolower($language)]->_url_selection = array();
                    }
                    $this->campingi18nInfoNew[strtolower($language)]->_url_selection[] = $objectContractUrl->url;
                } else {
                    $this->campingi18nInfoNew[strtolower($language)] = (object)array(
                        "id" => $campingId ,
                        "code" => strtolower($language) ,
                        "_short_description" => "",
                        "_long_description" => "",
                        "_url_reservation" => array(),
                        "_urls_data" => array(),
                        "_logos_data" => array(),
                        "_url_favorite" => array(),
                        "_url_selection" => array($objectContractUrl->url),
                    );
                }

                if(trim($objectContractUrl->url) != "" && $objectContractUrl->url != null) {
                    $saveTabCodeLangue[] = strtolower($language);
                }
            }

            // nous allons prendre pour reference l'url du contrat en fr, et le dupliquer dans les autres langues
            // si l'url est vide
            if(array_key_exists("fr", $this->campingi18nInfoNew) && property_exists($this->campingi18nInfoNew["fr"], "_url_selection") && count($this->campingi18nInfoNew["fr"]->_url_selection) != 0) {
                $_url_con = $this->campingi18nInfoNew["fr"]->_url_selection[count($this->campingi18nInfoNew["fr"]->_url_selection) - 1];
                foreach ($this->list_code_langue as $code_langue) {
                    if(! in_array($code_langue, $saveTabCodeLangue)){
                        if(array_key_exists(strtolower($code_langue), $this->campingi18nInfoNew)) {
                            $this->campingi18nInfoNew[strtolower($code_langue)]->_url_selection = array($_url_con);
                        } else {
                            $this->campingi18nInfoNew[strtolower($code_langue)] = (object)array(
                                "id" => $campingId,
                                "code" => strtolower($code_langue),
                                "_short_description" => "",
                                "_long_description" => "",
                                "_url_reservation" => array(),
                                "_urls_data" => array(),
                                "_logos_data" => array(),
                                "_url_favorite" => array(),
                                "_url_selection" => array($_url_con),
                            );
                        }
                    }
                }
            }
        }
    }

    /**
    * function which process Contrat Video
    * @param  int  $campingId camping identifier
    * @return void
    */
    private function traitementContratVideo(Contract $contract, $campingId) {
        $this->hasVideo = true;
    }

    /**
    * function which process _logo_data
    * @param  int  $campingId camping identifier
    * @return void
    */
    private function traitementLogo($campingId) {
        $arrayLogoAldreadyUsed = array();
        $daoCarac = $this->serviceloc->get('RepriseBatch\Model\CaracteristicTable');
        $daoGP = $this->serviceloc->get('RepriseBatch\Model\GoodPlanTable');


        // ajoute du logo bon plan
        foreach (array_merge(array('fr'), $this->list_code_langue) as $key => $value) {
            if($daoGP->countGoodPlanActifByCamping($campingId, $value) > 0) {
                if(array_key_exists(strtolower($value), $this->campingi18nInfoNew)) {
                    if(! is_array($this->campingi18nInfoNew[strtolower($value)]->_logos_data)){
                        $this->campingi18nInfoNew[strtolower($value)]->_logos_data = array();
                    }
                    $this->campingi18nInfoNew[strtolower($value)]->_logos_data[] = (object)array(
                        "logo" => URL_LOGO_REDUC,
                        "alt" => "Reduction",
                        "url" => "",
                        "partner" => 0
                    );
                } else {
                    $this->campingi18nInfoNew[strtolower($value)] = (object)array(
                        "id" => $campingId ,
                        "code" => strtolower($value) ,
                        "_short_description" => "",
                        "_long_description" => "",
                        "_url_reservation" => array(),
                        "_urls_data" => array(),
                        "_logos_data" => array(
                            (object)array(
                                "logo" => URL_LOGO_REDUC,
                                "alt" => "Reduction",
                                "url" => "",
                                "affichage" => 1,
                                "partner" => 0
                            )
                        ),
                        "_url_favorite" => array(),
                        "_url_selection" => array(),
                    );
                }
            }
        }

        // remplissage des logo caracteristiques
        $temp = $daoCarac->getCampingCaracteristicWithLogo($campingId);

        if(is_object($temp)) {
            $obj = $this->dao->getCamping($campingId);
            foreach ($temp as $key => $value) {
                if($value->logo_display == 1 || $value->logo_display == 2){
                    if(!array_key_exists($value->code, $arrayLogoAldreadyUsed) || !in_array($value->logo_path, $arrayLogoAldreadyUsed[$value->code])) {
                        if(array_key_exists(strtolower($value->code), $this->campingi18nInfoNew)) {
                            if(! is_array($this->campingi18nInfoNew[strtolower($value->code)]->_logos_data)){
                                $this->campingi18nInfoNew[strtolower($value->code)]->_logos_data = array();
                            }
                            $this->campingi18nInfoNew[strtolower($value->code)]->_logos_data[] = (object)array(
                                "logo" => $value->logo_path,
                                "alt" => (($value->label_sup != '')?$value->label_sup:(($value->label_sup_origin != '')?$this->label_sup_origin:(($value->label != '') ? $value->label : $value->label_origin))) ,
                                "url" => (($value->xml_tag == "LPBCAMPING")?"http://www.les-plus-beaux-campings.com/getcamping/".$obj->reference:$value->label_url),
                                "affichage" => $value->logo_display,
                                "partner" => 0
                            );
                        } else {
                            $this->campingi18nInfoNew[strtolower($value->code)] = (object)array(
                                "id" => $campingId ,
                                "code" => strtolower($value->code) ,
                                "_short_description" => "",
                                "_long_description" => "",
                                "_url_reservation" => array(),
                                "_urls_data" => array(),
                                "_logos_data" => array(
                                    (object)array(
                                        "logo" => $value->logo_path,
                                        "alt" => (($value->label_sup != '')?$value->label_sup:(($value->label_sup_origin != '')?$this->label_sup_origin:(($value->label != '') ? $value->label : $value->label_origin))) ,
                                        "url" => (($value->xml_tag == "LPBCAMPING")?"http://www.les-plus-beaux-campings.com/getcamping/".$obj->reference:$value->label_url),
                                        "affichage" => $value->logo_display,
                                        "partner" => 0
                                    )
                                ),
                                "_url_favorite" => array(),
                                "_url_selection" => array(),
                            );
                        }
                        if(!array_key_exists($value->code, $arrayLogoAldreadyUsed)) {
                            $arrayLogoAldreadyUsed[$value->code] = array();
                        }
                        $arrayLogoAldreadyUsed[$value->code][] = $value->logo_path;
                    }
                }
            }
        }

        // remplissage des logo issus du logo partenaire (les logos sont censes etre active avant)
        $res2 = $this->dao->getCampingLogoActifInformation($campingId);
        if(is_object($res2)){
            $retourinfo = array();
            foreach ($res2 as $key => $value) {
                if($value->chaine != 1) {
                    continue;
                }

                /* recuperation informations partenaires */
                $partenaire_info = null;
                $daoPartner = $this->serviceloc->get('RepriseBatch\Model\PartnerTable');
                try {
                    $partenaire_obj = $daoPartner->getById($value->partner_id);
                    if(is_object($partenaire_obj) && $partenaire_obj->id == $value->partner_id) {
                        $partenaire_info = $partenaire_obj;
                    }
                }catch (\Exception $e){}


                if(! array_key_exists($value->id, $retourinfo)) {
                    // enregistrement de l object de base
                    $retourinfo[$value->id] = array(
                        "path" => $value->path,
                        "url_default " => $value->url_default,
                        "partner_info" => ["id" => ((is_object($partenaire_info))?$partenaire_info->id:null),"label" => ((is_object($partenaire_info))?$partenaire_info->label:null)],
                        "langue" => array()
                    );

                    // traitemenent des data de logo_url
                    $data = json_decode($value->data, true);
                    if(json_last_error() == JSON_ERROR_NONE) {
                        if(is_array($data) && array_key_exists('listeUrl', $data)) {
                            foreach ($data['listeUrl'] as $langue => $object) {
                                if(! array_key_exists($langue, $retourinfo[$value->id]["langue"])) {
                                    $retourinfo[$value->id]["langue"][$langue] = array("description"=> "", "lien" => array());
                                }
                                $retourinfo[$value->id]["langue"][$langue]["lien"] = $object['lien'];
                            }
                        }
                    }
                }

                if($value->code != "" && $value->code != null) {
                    $retourinfo[$value->id]["langue"][strtolower($value->code)]["description"] = $value->teaser_long;
                }
            }

            foreach ($retourinfo as $logoId => $object) {
                foreach ($object["langue"] as $key => $infoLangue) {
                    if(array_key_exists(strtolower($key), $this->campingi18nInfoNew)) {
                        if(! is_array($this->campingi18nInfoNew[strtolower($key)]->_logos_data)){
                            $this->campingi18nInfoNew[strtolower($key)]->_logos_data = array();
                        }
                        $this->campingi18nInfoNew[strtolower($key)]->_logos_data[] = (object)array(
                            "logo" => $object['path'],
                            "alt" => $infoLangue["description"],
                            "url" =>  (!empty($infoLangue['lien'])) ? $infoLangue['lien'] : $object['url_default'],
                            "affichage" => 2,
                            "partner" => 1,
                            "partner_info" => $object["partner_info"]
                        );
                    } else {
                        $this->campingi18nInfoNew[strtolower($key)] = (object)array(
                            "id" => $campingId ,
                            "code" => strtolower($key) ,
                            "_short_description" => "",
                            "_long_description" => "",
                            "_url_reservation" => array(),
                            "_urls_data" => array(),
                            "_logos_data" => array(
                                (object)array(
                                    "logo" => $object['path'],
                                    "alt" => $infoLangue["description"],
                                    "url" =>  (!empty($infoLangue['lien'])) ? $infoLangue['lien'] : $object['url_default'],
                                    "affichage" => 2,
                                    "partner" => 1,
                                    "partner_info" => $object["partner_info"]
                                )
                            ),
                            "_url_favorite" => array(),
                            "_url_selection" => array(),
                        );
                    }
                }
            }
        }
    }

    /**
    * function which process Contrat Reservation
    * @param  Contract $contract  object contract
    * @param  int  $campingId camping identifier
    * @return void
    */
    private function traitementContratReservation(Contract $contract, $campingId) {
        $data = json_decode($contract->data, true);
        if(json_last_error() == JSON_ERROR_NONE) {
            /* recuperation des informations du partenaire */
            $part = null;
            $follow = "nofollow_link";
            if($contract->parent_id != null) {
                try {
                    $objC = $this->daoContract->getById($contract->parent_id);
                    if(is_object($objC) && $objC->partner_id != NULL){
                        /* recuperation du follow ou non des lien reservation */
                        if($objC->type != null && in_array($objC->type, array('nofollow_link', 'follow_link'))) {
                            $follow = $objC->type;
                        }

                        $objPart = $this->serviceloc->get('RepriseBatch\Model\PartnerTable')->getById($objC->partner_id);
                        if(is_object($objPart)) {
                            $part = $objPart;
                        }
                    }
                } catch(\Exception $e){}
            }

            if($data){
                $saveTabCodeLangue = array();
                foreach ($data as $language => $url) {
                    $url = trim($url);
                    if(!empty($url)) {
                        if (array_key_exists(strtolower($language), $this->campingi18nInfoNew)) {
                            /*if(! is_array($this->campingi18nInfoNew[strtolower($language)]->_url_reservation)){
                                $this->campingi18nInfoNew[strtolower($language)]->_url_reservation = array();
                            }*/
                            $this->campingi18nInfoNew[strtolower($language)]->_url_reservation = (object)array(
                                "url" => $url,
                                "id_partner" => ((is_object($part)) ? $part->reference : null),
                                "lab_partner" => ((is_object($part)) ? $part->label : null),
                                "follow" => (($follow == "follow_link")?"yes":"no")
                            );
                        } else {
                            $this->campingi18nInfoNew[strtolower($language)] = (object)array(
                                "id" => $campingId,
                                "code" => strtolower($language),
                                "_short_description" => "",
                                "_long_description" => "",
                                "_url_reservation" => (object)array(
                                    "url" => $url,
                                    "id_partner" => ((is_object($part)) ? $part->reference : null),
                                    "lab_partner" => ((is_object($part)) ? $part->label : null),
                                    "follow" => (($follow == "follow_link")?"yes":"no")
                                ),
                                "_urls_data" => array(),
                                "_logos_data" => array(),
                                "_url_favorite" => array(),
                                "_url_selection" => array(),
                            );
                        }

                        if(trim($url) != "" && $url != null) {
                            $saveTabCodeLangue[] = strtolower($language);
                        }
                    }
                }

                // nous allons prendre pour reference l'url du contrat en fr, et le dupliquer dans les autres langues
                // si l'url est vide
                if(array_key_exists("fr", $this->campingi18nInfoNew) && property_exists($this->campingi18nInfoNew["fr"], "_url_reservation")
                && is_object($this->campingi18nInfoNew["fr"]->_url_reservation) && property_exists($this->campingi18nInfoNew["fr"]->_url_reservation, "url")) {
                    $_url_con = $this->campingi18nInfoNew["fr"]->_url_reservation->url;
                    foreach ($this->list_code_langue as $code_langue) {
                        if(! in_array($code_langue, $saveTabCodeLangue)){
                            if(array_key_exists(strtolower($code_langue), $this->campingi18nInfoNew)) {
                                $this->campingi18nInfoNew[strtolower($code_langue)]->_url_reservation = (object)array("url" => $_url_con, "follow" => (($follow == "follow_link")?"yes":"no"));
                            } else {
                                $this->campingi18nInfoNew[strtolower($code_langue)] = (object)array(
                                    "id" => $campingId,
                                    "code" => strtolower($code_langue),
                                    "_short_description" => "",
                                    "_long_description" => "",
                                    "_url_reservation" => (object)array("url" => $_url_con, "follow" => (($follow == "follow_link")?"yes":"no")),
                                    "_urls_data" => array(),
                                    "_logos_data" => array(),
                                    "_url_favorite" => array(),
                                    "_url_selection" => array(),
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
    * function which active or desactive logo
    * @return void
    */
    private function activeLogo() {
        $daoPartner = $this->serviceloc->get('RepriseBatch\Model\PartnerTable');
        $daoPartner->activeDesactiveAll();
    }

    /**
    * function to active / desactive photo for camping
    * @param  int  campingId Camping identifier
    * @return void
    */
    private function activePhoto($campingId) {
        $this->dao->activeDesactivePhoto($campingId, $this->nbr_phot_to_activate);

        $this->dao->updateMainCampingPhoto($campingId);
    }

    /**
    * function to active / desactive video for camping
    * @param  int  campingId Camping identifier
    * @return void
    */
    private function activeVideo($campingId) {
        $this->dao->activeDesactiveVideo($campingId, $this->hasVideo);
    }

}
