<?php
namespace RepriseBatch\Model;

class Thematic
{
    public $id;
    public $type_thematics_id;
    public $label_origin;

    public function __construct(){}
    
    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->type_thematics_id = (isset($data['type_thematics_id'])) ? $data['type_thematics_id'] : null;
        $this->label_origin = (isset($data['label_origin'])) ? $data['label_origin'] : null;
    }
}

    
    
    
    
    
