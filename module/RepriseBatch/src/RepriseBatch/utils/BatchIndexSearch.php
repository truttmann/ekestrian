<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RepriseBatch\utils;

use RepriseBatch\Model\IndexSearch;
use Zend\ServiceManager\ServiceManager;

require_once __DIR__.'/Util.php';

class BatchIndexSearch {
    private $serviceloc;
    private $logger;

    /**
    * constructor 
    * @param  ServiceManager $serviceloc manager de service
    * @return void
    */
    public function __construct(ServiceManager $serviceloc){
        $this->serviceloc = $serviceloc;
    }

    /**
    * function qui va lancer le remplissage de la table index search
    */
    public function init() {
        // premier étape, on vide la table flag_index_search
        // $fact = $this->serviceloc->get('RepriseBatch\Model\IndexSearchTable');
        // $fact->truncate($this->serviceloc->get('Zend\Db\Adapter\Adapter'));

        /*
            on va remplir la table avec les donnees des tables suivantes:
                - les campings (leurs libelles)
                - les commune (les libelles)
                - les site touristiques (les libelle des thematique qui sont dans le type thematique "site touristique")
            nous allons devoir enregistrer les informations dans les différentes langues
        */
        $this->traitementTable('camping','id', 'name', 500, true, '', true);

        $this->traitementTable('city','id', 'label_origin', 500, true, '', true);

        $this->traitementTable('thematic','id', 'label_origin', 500, true, "type_thematics_id IN (select id from type_thematics WHERE system & 1)");

    }

    /**
    * function which loop from $attribueClass of object $nomTable to save it in the table flat_index_search
    * @param string $nomTable           name of the origin table
    * @param string $attribute_data_id  attribute that we used for data_id
    * @param string $attribueClass      list of attributes that we must to save
    * @param int    $offset             offset for download object data
    * @param int    $i18n               if we must to get i18n translation
    * @param string $where              if we must to add so attribute into the sql where
    */
    private function traitementTable($nomTable, $attribute_data_id, $attribueClass, $offset, $i18n = false, $where = "", $libel_departement = false) {
        $factG = $this->serviceloc->get("RepriseBatch\Model\IndexSearchTable");
        if($nomTable == null || empty($nomTable)) {
            throw new \Exception("You must to said what table");            
        }
        if(count($attribueClass) == 0) {
            throw new \Exception("Not any attribute to check");
        }
        if($offset == null || empty($offset) || !is_int($offset)) {
            throw new \Exception("You must to said the offset");
        }
        
        $fact = $this->serviceloc->get("RepriseBatch\Model\LanguageTable");
        $listeIdExistant = array();
        $encoreUnTour = true;
        $countFait = 0;
        while($encoreUnTour == true) {
            //traitement des données
            $tab = null;
            if($i18n == false) {
                $fact = $this->serviceloc->get("RepriseBatch\Model\\".ucfirst($nomTable)."Table");
                $tab = $fact->fetchLimit($countFait, $offset);
            } else {
                $action = "getDataForIndexSeachLimit".$nomTable;
                $tab = $fact->$action($countFait, $offset, true);
            }

            if(is_array($tab)) {
                if(sizeof($tab) == 0) {
                    $encoreUnTour = false;
                }
                // TODO: Un tableau php avec les differents languages, si un language n est pas trouvé, il faut prendre le label_origin
                foreach ($tab as $k => $objInput) {
                    if((is_object($objInput) && property_exists($objInput, $attribueClass)) || ($i18n == true && is_array($objInput) && array_key_exists($attribueClass, $objInput))) {
                        $label_before_trad = ($i18n == true) ? ((array_key_exists("label", $objInput) && $objInput["label"] != "") ? $objInput["label"] : $objInput[$attribueClass]) : $objInput->$attribueClass;
                        $code_langue = ($i18n == true && array_key_exists("code", $objInput) && $objInput["code"] != "") ? $objInput["code"] : 'fr';
                        $id_obj = ($i18n == true) ? ((array_key_exists($attribute_data_id, $objInput)) ? $objInput[$attribute_data_id] : $objInput[$attribute_data_id]) : $objInput->$attribute_data_id;

                        // mise en place des departement dans les libelle 
                        $label_before_trad = $label_before_trad.(($libel_departement == true && ((array_key_exists("label_origin_trad", $objInput) && $objInput['label_origin_trad'] != "" ) || (array_key_exists("label_trad", $objInput) && $objInput['label_trad'] != "" ))) ? ((array_key_exists("label_trad", $objInput) && $objInput['label_trad'] != "" ) ? " (".$objInput['label_trad'].")" : " (".$objInput['label_origin_trad'].")") : "");

                        // verification que le label utiliser a bien change
                        $test = $factG->getIndexSearchLabel($nomTable, $id_obj, $code_langue);
                        
                        // sauvegarde de l'identifiant pour ne pas le supprimer à la fin
                        if(!in_array($id_obj, $listeIdExistant)){
                            $listeIdExistant[] = $id_obj;
                        }

                        if($test == null || $test != $label_before_trad) {
                            // suppression de tous ce qui concerne l'objet
                            if($test != null) {
                                $this->serviceloc->get('logger')->logApp("batch INDEX SEARCH, Suppression de tous ce qui concerne l'object : ".$nomTable." , id: ".$id_obj);
                                $factG->deleteItemIndex($nomTable, $id_obj, $code_langue);
                            }

                            $tempTabObj = nettoyageData($label_before_trad, $code_langue);
                            if($tempTabObj != null) {
                                foreach ($tempTabObj as $kkk => $valueInsert) {
                                    $obj = new IndexSearch();   
                                    $obj->keyword = $valueInsert;
                                    $obj->label = $label_before_trad;
                                    $obj->table_name = $nomTable;
                                    $obj->data_id = $id_obj;
                                    $obj->language_code = $code_langue;
                                    $factG->save($obj);
                                }                        
                            }
                        }
                    }
                }
            } else {
                $encoreUnTour = false;
            }
            $countFait += $offset;
        }
        $this->serviceloc->get('logger')->logApp("batch INDEX SEARCH, Suppression de tous ce qui concerne les objects suvante : ".$nomTable." , liste id: ".implode(',', $listeIdExistant));
        $factG->deleteItem($nomTable, implode(',', $listeIdExistant));
   }
}
