<?php
namespace RepriseBatch\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;

class GoodPlanTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
    * function to count how many good plan are active for a camping
    * @param  int $camping_id identifier of camping
    * @param  int $language_code language code
    */
    public function countGoodPlanActifByCamping($camping_id, $language_code) {
        $rowset = $this->tableGateway->select(function (Select $select) use($camping_id, $language_code) {
            $select->columns(array('requestCount' => new Expression("count('')")));
            if($language_code != "fr") {
                $select->join('good_plan_i18n', 'good_plan_i18n.id = good_plan.id', array(), 'inner');
                $select->where(' status = 1 AND camping_id = '.$camping_id.' AND good_plan_i18n.code = "'.$language_code.'"');
            } else {
                $select->where(' status = 1 AND camping_id = '.$camping_id);
            }
        });
        $res = $rowset->getDataSource()->next();
        if(! is_array($res) || count($res) !=1 || !array_key_exists('requestCount', $res)) {
            return 0;
        }

        return $res['requestCount'];
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
    * find good plan by id
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
    * function to active a goodplan
    * @param  GoodPlan $goodplan object
    * @return void
    *
    */
    public function active(GoodPlan $goodplan) {
        $this->tableGateway->update(array("status" => 1), array("id" => $goodplan->id));
    }

    /**
    * function to active a goodplan
    * @param  array $listCampingId list of camping identifier
    * @param  array $listPartnerId list of partner identifier
    * @return void
    */
    public function activeDesactiveAll(array $listId) {
        $where = (!empty($listId))?" id IN (".implode(",", $listId).") ":"";
        if($where != "") {
            $this->tableGateway->update(array("status" => 1), $where);
        }

        $where = (!empty($listId))?" id NOT IN (".implode(",", $listId).") ":"";
        if($where != "") {
            $this->tableGateway->update(array("status" => 0), $where);
        } else {
            $this->tableGateway->update(array("status" => 0));
        }
    }

    /**
    * function to desactive a goodplan
    * @param  GoodPlan $goodplan object
    * @return void
    *
    */
    public function desactive(GoodPlan $goodplan) {
        $this->tableGateway->update(array("status" => 0), array("id" => $goodplan->id));
    }

    /**
    * function to delete a goodplan
    * @param  GoodPlan $goodplan object
    * @return void
    *
    */
    public function delete(GoodPlan $goodplan) {
        $this->tableGateway->delete(array("id" => $goodplan->id));
    }

    /**
    * find contract goog plan active partenaire
    * @return resultSet
    * @throws \Exception
    */
    public function getActiveGoodPlanPartenaire()
    {
        if(!defined('PACKAGE_PRODUCT_BON')) {
            throw new \Exception("Undefined constant PACKAGE_PRODUCT_BON");            
        }
        $adapter = $this->tableGateway->getAdapter(); 
        $test = new TableGateway('good_plan', $adapter, null, null);
        $statement = $this->tableGateway->adapter->query("
            SELECT ct.id as contract_id, ct.partner_id, gp.*
            FROM contract ct
            INNER JOIN contract_department ctd on ctd.contract_id = ct.id
            INNER JOIN good_plan gp on ctd.department_id = gp.department_id AND ct.partner_id = gp.partner_id
            WHERE ct.date_start <= '".date('Y-m-d')."' AND ct.date_end >= '".date('Y-m-d')."' AND ct.package_products_id = ".
            PACKAGE_PRODUCT_BON." AND ct.partner_id IS NOT NULL AND gp.date_start <= '".date('Y-m-d')."' AND gp.date_end >= '".date('Y-m-d')."'
            order by ct.id, ct.partner_id, department_id");
        $result = $statement->execute();
        return $result;
    }

    /**
    * find contract goog plan active camping
    * @return resultSet
    * @throws \Exception
    */
    public function getActiveGoodPlanCamping()
    {
        if(!defined('PACKAGE_PRODUCT_BON')) {
            throw new \Exception("Undefined constant PACKAGE_PRODUCT_BON");            
        }
        $adapter = $this->tableGateway->getAdapter(); 
        $test = new TableGateway('good_plan', $adapter, null, null);
        $statement = $this->tableGateway->adapter->query("
            SELECT ct.id as contract_id, ct.partner_id, gp.*
            FROM contract ct
            INNER JOIN good_plan gp on ct.camping_id = gp.camping_id
            WHERE ct.date_start <= '".date('Y-m-d')."' AND ct.date_end >= '".date('Y-m-d')."' AND ct.package_products_id = ".
            PACKAGE_PRODUCT_BON." AND ct.camping_id IS NOT NULL AND ct.partner_id IS NULL AND gp.date_start <= '".date('Y-m-d')."' AND gp.date_end >= '".date('Y-m-d')."'
            order by ct.id, camping_id");
        $result = $statement->execute();
        return $result;
    }

    /**
    * function to activate a good plan
    * @param  int   $id
    * @return void
    */
    public function activeGoodPlan($id) {
        $adapter = $this->tableGateway->getAdapter(); 
        $test = new TableGateway('good_plan', $adapter, null, null);
        $statement = $this->tableGateway->adapter->query("
            UPDATE good_plan
            SET status = 1
            WHERE id = $id
        ");
        $statement->execute();
        return;
    }
}