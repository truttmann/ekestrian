<?php
namespace RepriseBatch\Model;

class Partner
{
    public $id;
    public $reference;
    public $label;
    public $created_at;
    public $updated_at;
     
    public function __construct(){}

    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->reference = (isset($data['reference'])) ? $data['reference'] : null;
        $this->label = (isset($data['label'])) ? $data['label'] : null;
        $this->created_at = (isset($data['created_at'])) ? $data['created_at'] : null;
        $this->updated_at = (isset($data['updated_at'])) ? $data['updated_at'] : null;
    }
}

    
    
    
    
    
