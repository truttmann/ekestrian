<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Code\Reflection;

class ImageTable 
{
    protected $tableGateway;
    protected $prototype;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->prototype = $this->tableGateway->getResultSetPrototype()->getArrayObjectPrototype();
    }

    /**
    * list all object
    * @return resultSet
    */
    public function fetchAll($where = array(), $order = "filename")
    {
        $resultSet = $this->tableGateway->select(function (\Zend\Db\Sql\Select $select) use($where, $order){
            $select->where($where);
            $select->order($order);
        });
        return $resultSet;
    }
    
    /**
    * list all object
    * @return resultSet
    */
    public function fetchAllPaginate($filters = array(), $order = null)
    {
        $select = new Select($this->tableGateway->getTable());
        
        $this->setFiltersToSelect($select, $filters);

        $this->setOrderToSelect($select, $order);

        return $this->getPaginator($select);
    }

    /**
    *   find User by Id
    *   @param int id id of searched object
    */
    public function fetchOne($id){
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('image_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }


    /**
    * function to save a acl
    * @param  User $pays object
    * @return User
    */
    public function save(Image $image)
    {
        $data = array(
            "lot_id" => $image->lot_id,
            "filename" => $image->filename
        );

        $id = (int)$image->image_id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $image->image_id = $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->fetchOne($id)) {
                $this->tableGateway->update($data, array('image_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
        return $image;
    }
    
    public function delete(Image $image) {
        /* Suppression du cheval */
        $this->tableGateway->delete(array('image_id' => $image->image_id));
        return ;
    }
}