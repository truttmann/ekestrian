<?php
namespace RepriseBatch\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

class CaracteristicTable
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
    *   list Camping caracterisitic with logo
    *   @param int $id id of searched object
    */
    public function getCampingCaracteristicWithLogo($idCamp){
        $adapter = $this->tableGateway->getAdapter(); 
        $temp = new TableGateway('language', $adapter, null ,null);
        $resultSet = $temp->select(function (Select $select) use($idCamp) {
            $select->columns(array("code"));
            $select->join("caracteristic", "caracteristic.id = caracteristic.id", array(
                "id",
                "xml_tag",
                "label_origin" => new Expression("GROUP_CONCAT(caracteristic.label_origin SEPARATOR  ', ')"),
                "label_sup_origin" => new Expression("GROUP_CONCAT(caracteristic.label_sup_origin SEPARATOR  ', ')"),
                "logo_path",
                "label_url" => new Expression("GROUP_CONCAT(caracteristic.label_url SEPARATOR  ', ')"),
                "logo_display"),
            "inner");
            $select->join("caracteristic_i18n", "caracteristic_i18n.id = caracteristic.id AND caracteristic_i18n.code = language.code", array(
                "label" => new Expression("GROUP_CONCAT(caracteristic_i18n.label SEPARATOR  ', ')"),
                "label_sup" => new Expression("GROUP_CONCAT(DISTINCT caracteristic_i18n.label_sup SEPARATOR  ', ')")),
            "left");
            $select->join("camping_caracteristic", "caracteristic.id = camping_caracteristic.caracteristic_id", array(), "inner");
            $select->where("camping_id = ".$idCamp." AND logo_path IS NOT NULL AND logo_path != ''");
            $select->group(array("language.code","caracteristic.logo_path","caracteristic.logo_display"));
            $select->order("logo_display, caracteristic.ordering ASC");
        });
        return $resultSet;
    }

    /**
    * list caracterisitc label witch is linked directly on caracteristics'group
    * @return resultSet
    */
    public function getCaracLinkedGroupCara() {
        $adapter = $this->tableGateway->getAdapter(); 
        $logo = new TableGateway('group_caracteristics', $adapter); 
        $resultSet = $logo->select(" attribute_name is not null");
        return $resultSet;
    }

    /**
    * list all caracteristic suffixe
    * @return resultSet
    */
    public function getCaracteristicSuffixe() {
        $resultSet = $this->tableGateway->select(" suffix_xml_tag_year is not null and suffix_xml_tag_year != ''");
        return $resultSet;
    }
}