<?php
namespace RepriseBatch\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class ContractTable
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
    * find contract by id
    * @param int $id reference which is used for search
    * @return resultSet
    */
    public function getById($id)
    {
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
        }
        return $row;
    }

    /**
    * find contract by ref
    * @param int $ref reference which is used for search
    * @return resultSet
    */
    public function getByRef($ref)
    {
        $resultSet = $this->tableGateway->select(array('reference' => $ref));
        return $resultSet;
    }

    /**
    * find contract by partnerId
    * @param int $partnerId partner identifier
    * @return resultSet
    * @throws \Exception
    */
    public function getNonPassedReservationByPartner($partnerId)
    {
        if(!defined('PACKAGE_PRODUCT_RESERVATION')) {
            throw new \Exception("Undefined constant PACKAGE_PRODUCT_RESERVATION");            
        }

        $resultSet = $this->tableGateway->select('partner_id = '.$partnerId.' AND date_end > \''.date('Y-m-d').'\' AND package_products_id = '.PACKAGE_PRODUCT_RESERVATION);
        return $resultSet;
    }

    /**
    * find contract goog plan active
    * @return resultSet
    * @throws \Exception
    */
    public function getActiveGoodPlan()
    {
        if(!defined('PACKAGE_PRODUCT_BON')) {
            throw new \Exception("Undefined constant PACKAGE_PRODUCT_BON");            
        }
        $resultSet = $this->tableGateway->select(' date_start <= \''.date('Y-m-d').'\' AND date_end >= \''.date('Y-m-d').'\' AND (package_products_id = '.PACKAGE_PRODUCT_BON.')');
        return $resultSet;
    }

    /**
    * find contract camping active
    * @param  int       $campingId camping identifier
    * @return resultSet
    * @throws \Exception
    */
    public function getActiveContractCamping($campingId)
    {
        $resultSet = $this->tableGateway->select(' date_start <= \''.date('Y-m-d').'\' AND date_end >= \''.date('Y-m-d').'\' AND camping_id = '.$campingId);
        return $resultSet;
    }

    /**
    * find contract camping from a contractPartner
    * @param int $contractParentId parent contract identifier
    * @return resultSet
    * @throws \Exception
    */
    public function getContractByParent($contractParentId)
    {
        $resultSet = $this->tableGateway->select('parent_id = '.$contractParentId);
        return $resultSet;
    }

    /**
    * function which return all contracts containing URL (id = 1)
    * @param  int   $idProductUrl identifier of url product
    * @return mixed
    */
    public function getContractWithUrl($idProductUrl) {
        if(! is_int($idProductUrl)){
            throw new \Exception("Invalide param");            
        }
        $resultSet = $this->tableGateway->select(function (Select $select) use ($idProductUrl) {
            $select->join("package_products_product", "package_products_product.package_products_id = contract.package_products_id", array(), "inner");
            $select->where("package_products_product.produit_id = ".$idProductUrl." AND NOW() between date_start and date_end");
            $select->group(array("package_products_id","camping_id","partner_id"));
            $select->order("date_start ASC");
            
        });
        return $resultSet;
    }

    /**
    * function to save a contract
    * @param  Contract $carac object
    * @return Contract
    */
    public function save(Contract $contract)
    {
        $data = array(
            "id" => $contract->id,
            "camping_id" => $contract->camping_id,
            "partner_id" => $contract->partner_id,
            "package_products_id" => $contract->package_products_id,
            "logo_id" => $contract->logo_id,
            "parent_id" => $contract->parent_id,
            "reference" => $contract->reference,
            "date_start" => $contract->date_start,
            "date_end" => $contract->date_end,
            "data" => $contract->data,
            "created_at" => $contract->created_at,
            "updated_at" => $contract->updated_at
        );
         if ($contract->id != null && $contract->id != "" && $this->getById($contract->id)) {
             $this->tableGateway->update($data, array('id' => $contract->id));
         } else {
             $data['created_at'] = date('Y-m-d');
             $this->tableGateway->insert($data);
            $contract->id = $this->tableGateway->getLastInsertValue();
        }return $contract;
    }

    /**
    * function to delete a contract
    * @param  Contract $carac object
    * @return void
    *
    */
    public function delete(Contract $contract) {
        $this->tableGateway->delete(array("id" => $contract->id));
    }


    /**
    * function to get all contract_thematic about one contract
    * @param  Contract $carac object
    */
    public function getContractThematicFromContract(Contract $contract) {
        $adapter = $this->tableGateway->getAdapter(); 
        $contractthematic = new TableGateway('contract_thematic', $adapter); 
        $resultSet = $contractthematic->select(array('contract_id' => $contract->id));
        return $resultSet;
    }
}