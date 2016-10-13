<?php
namespace RepriseBatch\Model;

use Zend\Db\TableGateway\TableGateway;

class RegionTable
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
    public function getRegion($id){
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }


    public function save(Region $region)
    {
        $data = array(
            "country_id" => $region->country_id,
            "reference" => $region->reference,
            "insee" => $region->insee,
            "label_origin" => $region->label_origin,
            "coord_latitude" => $region->coord_latitude,
            "coord_longitude" => $region->coord_longitude,
            "coords_minimap" => $region->coords_minimap,
        );

        $id = (int)$region->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $region->id = $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getRegion($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
        return $region;
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