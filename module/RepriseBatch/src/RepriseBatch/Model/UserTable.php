<?php
namespace RepriseBatch\Model;

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

    /**
    *   function to link camping with its manager
    * @param  User $user      acl whitch will be rattache to the camping
    * @param  int  $idCamping camping identifier
    * @return void
    */
    public function linkToCamping(User $user, $idCamping) {
        $adapter = $this->tableGateway->getAdapter(); 
        $userCampingTable = new TableGateway('user_camping', $adapter); 
        $userCampingTable->insert(array(
            "camping_id" => $idCamping,
            "user_id" => $user->id
        ));
    }

    /**
    *   function to link role with its manager
    * @param  User $user      acl whitch will be rattache to the camping
    * @return void
    */
    public function saveRoleGestionnaire(User $user) {
        $adapter = $this->tableGateway->getAdapter(); 
        $userCampingTable = new TableGateway('user_role', $adapter); 
        $userCampingTable->insert(array(
            "user_id" => $user->id,
            "role_id" => ROLE_GESTIONNAIRE_CAMPING
        ));
    }

    /**
    *   function to link camping with its manager
    * @param  int  $idCamping camping identifier
    * @return void
    */
    public function getCampingManager($idCamping) {
        $idCamping  = (int) $idCamping;
        
        $rowset = $this->tableGateway->select(function(Select $select) use($idCamping) {
            $select->join('user_camping', 'user.id = user_camping.user_id', 'camping_id', 'inner');
            $select->where('camping_id = '.$idCamping);
        });
        $row = $rowset->current();
        if (!$row) {
            return null;
        }
        return $row;
    }
}