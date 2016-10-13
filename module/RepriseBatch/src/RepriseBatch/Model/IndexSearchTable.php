<?php
namespace RepriseBatch\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class IndexSearchTable
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
    * list object using limit
    * @param  int       $start
    * @param  int       $end
    * @return resultSet
    */
    public function fetchLimit($start, $offset=null)
    {
        if($start == null || !is_int($start)) {
            throw new \Exception("Invalid start value");            
        }
        $resultSet = $this->tableGateway->select(function (Select $select) {
            //$select->where->like('name', 'Brit%');
            //$select->order('name ASC')->limit(2);
            if($offset != null) {
                $select->order('data_id')->limit($start)->offset($offset);
            } else {
                $select->order('data_id')->limit($start);
            }
        });
        return $resultSet;
    }

    /**
    * delete all table data
    * @return void
    */
    public function deleteAll() {
        $this->tableGateway->delete(null);
    }

    /**
    *   find items by Id
    *   @param int id id of searched object
    */
    public function get($language_code, $keyword, $table_name, $data_id){
        $rowset = $this->tableGateway->select(array(
            "language_code" => $language_code,
            "keyword" => $keyword,
            "table_name" => $table_name,
            "data_id" => $data_id,
        ));
        $row = $rowset->current();
        if (!$row) {
            return null;
        }
        return $row;
    }

    /**
    * function to save object in database
    * @param  IndexSearch $donnee object to save
    * @return IndexSearch
    * @throws \Exception 
    */
    public function save(IndexSearch $donnee)
    {
        $data = array(
            "keyword" => $donnee->keyword,
            "label" => $donnee->label,
            "table_name" => $donnee->table_name,
            "data_id" => $donnee->data_id,
            "language_code" => $donnee->language_code,
        );

        if($this->get($donnee->language_code, $donnee->keyword, $donnee->table_name, $donnee->data_id) == null) {
            $this->tableGateway->insert($data);
        }

            /*if ($this->getCamping($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }*/
        return $donnee;
    }

    /**
    * function witch git the label object saved for index search table
    *   @param  string $table_name  property of items searched
    *   @param  int    $data_id     property of items searched
    *   @param  string $code_langue property of items searched
    *   @return mixed
    */    
    public function getIndexSearchLabel($table_name, $data_id, $code_langue) {
        $rowset = $this->tableGateway->select(array(
            "table_name" => $table_name,
            "data_id" => $data_id,
            "language_code" => $code_langue,
        ));
        $res = $rowset->getDataSource()->next();
        if(! is_array($res) || !array_key_exists('label', $res)) {
            return null;
        }
        return $res['label'];
    }

    /**
    * function witch remove all informations about one items
    *   @param  string $table_name  property of items searched
    *   @param  int    $data_id     property of items searched
    *   @param  string $code_langue property of items searched
    *   @return void
    */    
    public function deleteItemIndex($table_name, $data_id, $code_langue) {
        $rowset = $this->tableGateway->delete(array(
            "table_name" => $table_name,
            "data_id" => $data_id,
            "language_code" => $code_langue,
        ));
    }

    /**
    * function witch remove all informations about one items
    *   @param  string $table_name property of items searched
    *   @param  string $data_id property of items searched
    *   @return void
    */    
    public function deleteItem($table_name, $data_id) {
        if(empty($data_id)) {
            $data_id = "0";
        }
        $rowset = $this->tableGateway->delete(" table_name = '".$table_name."' AND data_id NOT IN (".$data_id.") ");
    }

    /**
    * truncate table
    * @param Adapter $adapter
    */
    public function truncate(Adapter $adapter) {
        $adapter->query("TRUNCATE _index_search")->execute() ;
    }
}