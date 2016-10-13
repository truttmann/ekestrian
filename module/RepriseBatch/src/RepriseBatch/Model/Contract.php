<?php
namespace RepriseBatch\Model;

class Contract
{
    public $id;
    public $camping_id;
    public $partner_id;
    public $package_products_id;
    public $type;
    public $logo_id;
    public $parent_id;
    public $reference;
    public $date_start;
    public $date_end;
    public $data;
    public $created_at;
    public $updated_at;

    public function __construct(){}
    
    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;        
        $this->camping_id = (isset($data['camping_id'])) ? $data['camping_id'] : null;        
        $this->partner_id = (isset($data['partner_id'])) ? $data['partner_id'] : null;        
        $this->package_products_id = (isset($data['package_products_id'])) ? $data['package_products_id'] : null;        
        $this->type = (isset($data['type'])) ? $data['type'] : null;        
        $this->reference = (isset($data['reference'])) ? $data['reference'] : null;        
        $this->logo_id = (isset($data['logo_id'])) ? $data['logo_id'] : null;        
        $this->parent_id = (isset($data['parent_id'])) ? $data['parent_id'] : null;        
        $this->date_start = (isset($data['date_start'])) ? $data['date_start'] : null;        
        $this->date_end = (isset($data['date_end'])) ? $data['date_end'] : null;        
        $this->data = (isset($data['data'])) ? $data['data'] : null;        
        $this->created_at = (isset($data['created_at'])) ? $data['created_at'] : null;        
        $this->updated_at = (isset($data['updated_at'])) ? $data['updated_at'] : null;        
    }
}   