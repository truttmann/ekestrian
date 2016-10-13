<?php
namespace RepriseBatch\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class CityTable
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
    * find pays by ref
    * @param int $ref reference which is used for search
    * @return resultSet
    */
    public function getByRef($ref)
    {
        $resultSet = $this->tableGateway->select(array('reference' => $ref));
        return $resultSet;
    }

    /**
    *   find Commune by Id
    *   @param int id id of searched object
    */
    public function getCommune($id){
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    /**
    * list object using limit
    * @param  int       $start
    * @param  int       $end
    * @return resultSet
    */
    public function fetchLimit($offset, $start=null)
    {
        if($start === null || !is_int($start)) {
            throw new \Exception("Invalid start value: $start");            
        }
        $resultSet = $this->tableGateway->select(function (Select $select) use ($start,$offset) {
            //$select->where->like('name', 'Brit%');
            //$select->order('name ASC')->limit(2);
            if($offset != null) {
                $select->order('id')->limit($start)->offset($offset);
            } else {
                $select->order('id')->limit($start);
            }
        });
        return $resultSet;
    }

    /**
    * count all object
    * @return resultSet
    */
    public function count()
    {
        $rowset = $this->tableGateway->select(function (Select $select) {
            $select->columns(array('requestCount' => new Expression("count('')")));
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

    public function save(City $commune)
    {
        $data = array(
            "department_id" => $commune->department_id,
            "reference" => $commune->reference,
            "insee" => $commune->insee,
            "situation_origin" => $commune->situation_origin,
            "label_origin" => $commune->label_origin,
            "coord_latitude" => $commune->coord_latitude,
            "coord_longitude" => $commune->coord_longitude,
        );

        $id = (int)$commune->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $commune->id = $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getCommune($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
        return $commune;
    }

    /**
    * find city by libelle and libelle region
    * @param string $libelle libelle of city
    * @param string $libReg  libelle of city's region
    */
    public function findByLibelleAndLibRegion($libelle, $libReg) {
        $rowset = $this->tableGateway->select(function (Select $select) use($libelle, $libReg) {
            $select->join('department', 'department.id = department_id', array(), 'inner');
            $select->join('region', 'region.id = region_id', array(), 'inner');
            $select->where(array('LOWER(city.label_origin) = LOWER(?) AND LOWER(region.label_origin) = LOWER(?)' => array($libelle, $libReg)));
        });
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find city by lib and lib region : $libelle - $libReg");
        }
        return $row;
    }
}