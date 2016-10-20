<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Code\Reflection;

class ClientAuctionTable 
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
    public function fetchAll($where = array(), $order = "name")
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
    public function fetchAllPaginate($filters = array(), $order = null, $where = array())
    {
        $select = new Select($this->tableGateway->getTable());
        $select->join("lot", "lot.lot_id = client_auction.lot_id", array("lot_name" => "title"), "inner");
        $select->join("client", "client.client_id = client_auction.client_id", array("firstname", "lastname","email", "phone"), "inner");
        $select->join("country", "country.country_id = client.country_id", array("libelle"), "inner");
        $select->where($where);
        
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
        $rowset = $this->tableGateway->select(array('user_auction_id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
    
    public function getLastEnchere(Lot $lot){
        $rowset = $this->tableGateway->select(function(Select $select) use ($lot){
            $select->where(array('lot_id' => $lot->lot_id));
            $select->order("value DESC");
            $select->limit(1);
        });
        $row = $rowset->current();
        if (!$row) {
            return null;
        }
        return $row;
    }


    /**
    * function to save a acl
    * @param  User $pays object
    * @return User
    */
    public function save(ClientAuction $auction)
    {
        $data = array(
            "client_id" => $auction->client_id,
            "lot_id" => $auction->lot_id,
            "value" => $auction->value,
            "card_id" => $auction->card_id,
            "authorization_id" => $auction->authorization_id,
            "win" => (($auction->win == 1)?$auction->win:0),
        );

        $id = (int)$auction->user_auction_id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $auction->user_auction_id = $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->fetchOne($id)) {
                $this->tableGateway->update($data, array('user_auction_id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
        return $auction;
    }
    /**
     * Créer un objet Paginator en fonction de l'objet Select $select et du prototype par défaut de la TableGateway.
     * @param Select $select
     * @return Paginator
     */
    public function getPaginator(Select $select)
    {
        $paginatorAdapter = new \Zend\Paginator\Adapter\DbSelect($select, $this->tableGateway->getAdapter(), $this->tableGateway->getResultSetPrototype());
        $paginator = new \Zend\Paginator\Paginator($paginatorAdapter);
        return $paginator;
    }

    /**
     * Ajoute les clause Where à l'objet $select en fonction du tableau de filtres $filters donnés en paramètres.
     * Si la clé, donc le champ, est égal à 'id' ou contient '_id' alors on effectue un '=' au lieu d'un LIKE.
     * @param Select $select
     * @param array $filters La liste des filtres sous forme de Clé => Valeur
     * @throws \Exception Si le champ est un ID mais que la valeur n'est pas numeric.
     */
    public function setFiltersToSelect(Select $select, $filters)
    {
        if (!is_array($filters)) {
            return;
        }

        if (!$this->prototype instanceof \ArrayObject) {
            $prototype = $this->prototype->getAttributesNames();
        } else {
            $select->where($filters);
            return;
        }
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                $attrName = $field;
                $clauseType = 'where';
                if (isset($prototype[$field])) {
                    $attrName = $this->prototype->getAttributesNames()[$field];
                }
                $rfl = $this->getAttributeDetails($attrName);

                // On récupère le libellé ou le code de l'attribut
                $label = $field;
                if ($rfl->hasTag('Label')) {
                    $label = $rfl->getTag('Label')->getContent();
                }

                // On considère que le type de l'attribut est une string par défaut.
                $type = 'string';
                if ($rfl->hasTag('var')) {
                    $type = $rfl->getTag('var')->getContent();
                }

                // Si on a un @Code, alors on l'utilise à la place du champ de base.
                // Utile pour les alias lorsque l'on a des données joint à la table de base.
                if ($rfl->hasTag('Code')) {
                    $clauseType = 'having';
                    $field = $rfl->getTag('Code')->getContent();
                } else {
                    $field = $this->tableGateway->getTable() . '.' . $field;
                }

                if ($type == 'integer' || $type == 'int') {
                    // Si la valeur n'est pas numerique
                    if (!is_numeric($value)) {
                        throw new \Exception($label . ' doit être un nombre');
                    }
                    $select->$clauseType(array($field => $value));
                } else {
                    $select->$clauseType->like($field, "%$value%");
                }
            } else if ($value === null) {
                $select->where(array($field => null));
            }
        }
    }

    /**
     * Ajoute la clause de tri à l'objet Select passé en paramètre
     * @param Select $select
     * @param string|array $order
     */
    public function setOrderToSelect(Select $select, $order)
    {
        if ($order) {
            $select->order($order);
        }
    }
    
    /**
     * @param $attrName
     * @return false|Reflection\DocBlockReflection
     * @throws \Exception S'il n'y a pas d'informations concernant l'attribut.
     */
    public function getAttributeDetails($attrName)
    {
        $propertyReflection = new Reflection\PropertyReflection($this->prototype, $attrName);
        if ($propertyReflection->getDocBlock() === false) {
            throw new \Exception("Missing doc informations on attribute '$attrName'");
        }
        return $propertyReflection->getDocBlock();
    }
}