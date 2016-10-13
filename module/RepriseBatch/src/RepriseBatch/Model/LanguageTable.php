<?php
namespace RepriseBatch\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class LanguageTable
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
    * list object translate for insert into index_search using limit
    * @param  int       $start
    * @param  int       $end
    * @return resultSet
    */
    public function getDataForIndexSeachLimitcity($offset, $start=null, $asArray = false)
    {
        $return = null;
        if($start === null || !is_int($start)) {
            throw new \Exception("Invalid start value: $start");            
        }
        $resultSet = $this->tableGateway->select(function (Select $select) use ($start,$offset) {
            $select->columns(array('code'=>new Expression('language.code'), 'label' => new Expression("CONCAT( coalesce(UPPER(city_i18n.prefix_le), ''), ' ', city_i18n.label )")), false);
            //$select->where->like('name', 'Brit%');
            //$select->order('name ASC')->limit(2);
            $select->join('city', 'city.id = city.id', array('label_origin', 'id'), 'left');
            $select->join('city_i18n', 'city.id = city_i18n.id AND city_i18n.code = language.code', array(), 'left');
            $select->join('department', 'city.department_id = department.id ', array('label_origin_trad'=> 'label_origin'), 'inner');
            $select->join('department_i18n', 'department.id = department_i18n.id AND department_i18n.code = language.code', array('label_trad'=> 'label'), 'left');
            $select->where('city.status IS NULL OR city.status = 1');
            if($offset != null) {
                $select->order('city.id')->limit($start)->offset($offset);
            } else {
                $select->order('city.id')->limit($start);
            }
        });
        if($asArray == true){
            foreach ($resultSet->getDataSource() as $key => $value) {
                $return[] = $value;
            }
        } else {
            $return = $resultSet;
        }
        return $return;
    }

    /**
    * list object translate for insert into index_search using limit
    * @param  int       $start
    * @param  int       $end
    * @return resultSet
    */
    public function getDataForIndexSeachLimitcamping($offset, $start=null, $asArray = false)
    {
        $return = null;
        if($start === null || !is_int($start)) {
            throw new \Exception("Invalid start value: $start");            
        }
        $resultSet = $this->tableGateway->select(function (Select $select) use ($start,$offset) {
            $select->columns(array('code'));
            //$select->where->like('name', 'Brit%');
            //$select->order('name ASC')->limit(2);
            $select->join('camping', 'camping.id = camping.id', array('name', 'id'), 'left');
            $select->join('city', 'camping.city_id = city.id ', array(), 'inner');
            $select->join('department', 'city.department_id = department.id ', array('label_origin_trad' => 'label_origin'), 'inner');
            $select->join('department_i18n', 'department.id = department_i18n.id AND department_i18n.code = language.code', array('label_trad'=> 'label'), 'left');
            $select->where('camping.status IS NULL OR camping.status = 1');
            if($offset != null) {
                $select->order('camping.id')->limit($start)->offset($offset);
            } else {
                $select->order('camping.id')->limit($start);
            }
        });
        if($asArray == true){
            foreach ($resultSet->getDataSource() as $key => $value) {
                $return[] = $value;
            }
        } else {
            $return = $resultSet;
        }
        return $return;
    }

    /**
    * list object translate for insert into index_search using limit
    * @param  int       $start
    * @param  int       $end
    * @return resultSet
    */
    public function getDataForIndexSeachLimitthematic($offset, $start=null, $asArray = false)
    {
        $return = null;
        if($start === null || !is_int($start)) {
            throw new \Exception("Invalid start value: $start");            
        }
        $resultSet = $this->tableGateway->select(function (Select $select) use ($start,$offset) {
            $select->columns(array('code'));
            //$select->where->like('name', 'Brit%');
            //$select->order('name ASC')->limit(2);
            $select->join('thematic', 'thematic.id = thematic.id', array('label_origin', 'id'), 'left');
            $select->join('thematic_i18n', 'thematic.id = thematic_i18n.id AND thematic_i18n.code = language.code', 'label', 'left');
            $select->join('type_thematics', 'thematic.type_thematics_id = type_thematics.id', array(), 'inner');
            $select->where('type_thematics.system & 1');
            if($offset != null) {
                $select->order('thematic.id')->limit($start)->offset($offset);
            } else {
                $select->order('thematic.id')->limit($start);
            }
        });
        if($asArray == true){
            foreach ($resultSet->getDataSource() as $key => $value) {
                $return[] = $value;
            }
        } else {
            $return = $resultSet;
        }
        return $return;
    }
}
