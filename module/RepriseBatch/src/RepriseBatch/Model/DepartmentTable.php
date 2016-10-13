<?php
namespace RepriseBatch\Model;

use Zend\Db\TableGateway\TableGateway;

class DepartmentTable
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
    * find pays by code
    * @param int $code code which is used for search
    * @return resultSet
    */
    public function getByCode($code)
    {
        $resultSet = $this->tableGateway->select(array('code' => $code));
        return $resultSet;
    }

    /**
    *   find Departement by Id
    *   @param int id id of searched object
    */
    public function getDepartement($id){
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }


    public function save(Department $departement)
    {
        $data = array(
            "region_id" => $departement->region_id,
            "reference" => $departement->reference,
            "code" => $departement->code,
            "insee" => $departement->insee,
            "label_origin" => $departement->label_origin,
            "coord_latitude" => $departement->coord_latitude,
            "coord_longitude" => $departement->coord_longitude,
        );

        $id = (int)$departement->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $departement->id = $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getDepartement($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
        return $departement;
    }

    /*public function getAlbum($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveAlbum(Album $album)
    {
        $data = array(
            'artist' => $album->artist,
            'title'  => $album->title,
        );

        $id = (int)$album->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getAlbum($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteAlbum($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }*/
}