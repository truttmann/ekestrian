<?php
namespace RepriseBatch\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

class CampingCaracteristicTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
    * list all object
    * @return resultSet
    */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    /**
    *   find Camping caracterisitic by Id
    *   @param int $idCamp  id of searched object
    *   @param int $idCarac id of searched object
    *   @param int $idGroupCarac id of searched object
    *   @return CampingCaracteristic
    */
    public function getCampingCaracteristic($idCamp, $idCarac, $idGroupCarac){
        $rowset = $this->tableGateway->select(array('camping_id' => $idCamp, 'caracteristic_id' => $idCarac, 'group_caracteristics_id' => $idGroupCarac));
        $row = $rowset->current();
        if (!$row) {
            //throw new \Exception("Could not find row $idCamp , $idCarac");
            return false;
        }
        return $row;
    }

    /**
    *   find Camping caracterisitic by IdCamp and label caracteristic
    *   @param int $idCamp      id of searched object
    *   @param int $TagXmlCarac label of caracteristic
    *   @return resultSet
    */
    public function getCampingCaracteristicByXmlTag($idCamp, $TagXmlCarac){
        $rowset = $this->tableGateway->select(function (Select $select) use($idCamp, $TagXmlCarac) {
            $select->columns(array('year'));
            $select->join("caracteristic", "caracteristic.id = camping_caracteristic.caracteristic_id", array(), "inner");
            $select->where->equalTo("camping_id", $idCamp)->equalTo("caracteristic.xml_tag", $TagXmlCarac);
        });
        $row = $rowset->current();
        if (!$row) {
            //throw new \Exception("Could not find row $idCamp , $idCarac");
            return false;
        }
        return $row->year;
    }

    /**
    * function to save a camping 's carateristics
    * @param  CampingCaracteristic $carac object
    * @return CampingCaracteristic
    */
    public function save(CampingCaracteristic $carac)
    {
        $data = array(
            "camping_id" => $carac->camping_id,
            "caracteristic_id" => $carac->caracteristic_id,
            "group_caracteristics_id" => $carac->group_caracteristics_id,
            "ad" => $carac->ad,
            "val" => $carac->val,
            "year" => $carac->year,
            "distance" => $carac->distance,
            "nom" => $carac->nom,
            "tarif" => $carac->tarif,
            "tarif2" => $carac->tarif2,
            "quantite" => $carac->quantite,
            "quantite2" => $carac->quantite2,
            "remarque" => $carac->remarque,
            "duree" => $carac->duree,
            "nbre_total" => $carac->nbre_total,
            "tourisme" => $carac->tourisme,
            "loisirs" => $carac->loisirs,
            "campingcar" => $carac->campingcar,
            "tente" => $carac->tente,
            "ouv1" => $carac->ouv1,
            "ouv2" => $carac->ouv2,
            "ouv3" => $carac->ouv3,
            "ouv4" => $carac->ouv4,
            "dates" => $carac->dates,
        );
         if ($row = $this->getCampingCaracteristic($carac->camping_id, $carac->caracteristic_id, $carac->group_caracteristics_id)) {
            $this->tableGateway->update($data, array("id" => $row->id));
            $carac->id = $row->id;
        } else {
            $this->tableGateway->insert($data);
            $carac->id = $this->tableGateway->getLastInsertValue();
        }
        return $carac;
    }

    /**
    * function to delete a list of caracteristics
    * @param int   $idCamping camping identifier
    * @param array $listCara  array of caracteristics identifier
    */
    public function deleteNotSave($idCamping, array $listCara) {
        if(! empty($listCara)) {
            $this->tableGateway->delete("
                camping_id = ".$idCamping." AND id NOT IN (".implode(",", $listCara).")
            ");
        }
    }
}