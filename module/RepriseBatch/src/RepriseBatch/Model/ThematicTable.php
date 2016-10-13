<?php
namespace RepriseBatch\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class ThematicTable
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
    * count all object
    * @param  string    $where attribute to add to the where clause
    * @return resultSet
    */
    public function count($where)
    {
        $rowset = $this->tableGateway->select(function (Select $select) use ($where) {
            $select->columns(array('requestCount' => new Expression("count('')")));
            if(! empty($where)) {
                $select->where($where);
            }
        });
        /*if (!$row) {
            throw new \Exception("Could not find row $id");
        }*/
        $res = $rowset->getDataSource()->next();
        if(! is_array($res) || count($res) !=1 || !array_key_exists('requestCount', $res)) {
            throw new \Exception("Unable to count data");            
        }

        return $res['requestCount'];
    }

    /**
    * list all city for oen thematic
    * @param  int $thematicId identifier of cthematic
    * @return resultSet
    */
    public function getCityByThematic($thematicId)
    {
        $adapter = $this->tableGateway->getAdapter(); 
        $city = new TableGateway('city', $adapter);
        $rowset = $city->select( function(Select $select) use($thematicId) {
            $select->join(array('tc' => 'thematic_city'), 'tc.city_id = city.id', array(), "inner");
            $select->where(array('tc.thematic_id = ?' => $thematicId));
        });
        return $rowset;
    }

    /**
    * Get thematic by label origin
    * @param  string $label label
    * @return resultSet
    */
    public function getByLibelleOrigin($label) { 
        $rowset = $this->tableGateway->select(function (Select $select) use ($label) {
            $select->where->like('label_origin', $label);
        });
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Undefined Thematic with label : ".$label);            
        }
        return $row;
    }

    /**
    * Insert a link between a thematic and a city
    * @param  int $thematicId identifier of cthematic
    * @return void
    */
    public function insertThematicCityLink($thematicId, $cityId) {
        $adapter = $this->tableGateway->getAdapter(); 
        $logo = new TableGateway('thematic_city', $adapter); 
        $logo->insert(array('thematic_id' => $thematicId, 'city_id' => $cityId));
    }

    /**
    * DELETE a link between a thematic and a city
    * @param  int $thematicId identifier of cthematic
    * @return void
    */
    public function deleteThematicCityLink($thematicId, $cityId) {
        $adapter = $this->tableGateway->getAdapter(); 
        $logo = new TableGateway('thematic_city', $adapter); 
        $logo->delete(array('thematic_id' => $thematicId, 'city_id' => $cityId));
    }
}