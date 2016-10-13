<?php
namespace RepriseBatch\Model;

use RepriseBatch\Model\Camping;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class PartnerTable
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
    * find partner by label
    * @param  string    $label label of partner
    * @return resultSet
    */
    public function getByLabel($label)
    {
        $rowset = $this->tableGateway->select(array('label' => $label));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $label");
        }
        return $row;
    }

    /**
     * find partner by id
     * @param  string    $id Identifier of partner
     * @return resultSet
     */
    public function getById($id)
    {
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $label");
        }
        return $row;
    }

    /**
    * delete all partner logo
    * @param  Partner $partner partner object
    * @return void
    */
    public function deleteAllLogo(Partner $partner) {
        $adapter = $this->tableGateway->getAdapter(); 
        $logo = new TableGateway('logo', $adapter); 
        $logo->delete(array('partner_id' => $partner->id));
    }

    /**
    * delete all partner logo
    * @param  int     $partner    partner object
    * @param  int     $camping_id camping object
    * @param  string  $code_lang  language code
    * @return void
    */
    public function deleteLogo($idLogo, $camping_id) {
        $adapter = $this->tableGateway->getAdapter(); 
        $logo = new TableGateway('logo_url', $adapter); 
        $logo->delete(array('id' => $idLogo, 'camping_id' => $camping_id));
    }


    /**
    * insert partner logo URL
    * @param  int     $idLogo      Logo identifier
    * @param  int     $campingid   camping object
    * @param  string  $url_default default url
    * @param  string  $data        data object
    * @return void
    */
    public function insertLogo($idLogo, $campingid, $url_default, $data) {
        $adapter = $this->tableGateway->getAdapter(); 
        $logo = new TableGateway('logo_url', $adapter); 
        $logo->insert(array(
            'id' => $idLogo,
            'camping_id' => $campingid,
            'data' => $data,
            'url_default' => $url_default
        ));
    }

    /**
    * update partner logo
    * @param  int     $idLogo      Logo identifier
    * @param  int     $campingid   camping object
    * @param  string  $url_default default url
    * @param  string  $data        data object
    * @return void
    */
    public function updateLogo($idLogo, $campingid, $url_default, $data) {
        $adapter = $this->tableGateway->getAdapter(); 
        $logo = new TableGateway('logo_url', $adapter); 
        $logo->update(array(
                'data' => $data
            ),array(
                'id' => $idLogo,
                'camping_id' => $campingid
            )
        );
    }

    /**
    * get partner logo
    * @param  Partner $partner   partner object
    * @return void
    */
    public function getLogoWithUrl(Partner $partner) {
        $adapter = $this->tableGateway->getAdapter(); 
        $logo = new TableGateway('logo', $adapter); 
        $resultSet = $logo->select(function (Select $select) use ($partner) {
            $select->columns(array('id'));
            $select->join("logo_url","logo.id = logo_url.id",array("id_logo_url" => "id", "url_default","data","blacklist","camping_id"),"left");
            $select->where("partner_id = ".$partner->id);
        });
        return $resultSet;
    }

    /**
    * function for check activity of logo
    */
    public function activeDesactiveAll() {
        $adapter = $this->tableGateway->getAdapter(); 
        $logo = new TableGateway('logo', $adapter); 
        $logo->update(array("status" => 1), " id IN (SELECT logo_id from contract where date_start <= '".date('Y-m-d')."' AND date_end >= '".date('Y-m-d')."');");
        $logo->update(array("status" => 0), " id NOT IN (SELECT logo_id from contract where date_start <= '".date('Y-m-d')."' AND date_end >= '".date('Y-m-d')."');");
    }
}