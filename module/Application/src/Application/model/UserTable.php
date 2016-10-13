<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class UserTable
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
    *   find User by Id
    *   @param int id id of searched object
    */
    public function getUser($id){
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
    
    /**
    *   find User by login Password
    *   @param int id id of searched object
    */
    public function getByLoginPass($login, $password){
        $rowset = $this->tableGateway->select(array('email' => $login, 'password' => $password));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $login");
        }
        return $row;
    }


    /**
    * function to save a acl
    * @param  User $pays object
    * @return User
    */
    public function save(User $user)
    {
        $data = array(
            "login" => $user->login,
            "password" => $user->password,
            "name" => $user->name,
            "created_at" => $user->created_at,
            "updated_at" => $user->updated_at,
        );

        $id = (int)$user->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $user->id = $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getUser($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
        return $user;
    }
}