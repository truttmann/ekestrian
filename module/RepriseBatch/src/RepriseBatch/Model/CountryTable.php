<?php
namespace RepriseBatch\Model;

use Zend\Db\TableGateway\TableGateway;

class CountryTable
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
    *   find Pays by Id
    *   @param int id id of searched object
    */
    public function getPays($id){
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function save(Country $pays)
    {
        $data = array(
            'reference'  => $pays->reference,
            'label_origin' => $pays->label_origin,
            'iso' => $pays->iso,
        );

        $id = (int)$pays->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $pays->id = $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getPays($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
        return $pays;
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