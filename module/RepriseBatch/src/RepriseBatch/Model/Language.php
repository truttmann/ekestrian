<?php
namespace RepriseBatch\Model;

class Language
{
    public $code;
    public $label;

    public function __construct(){}
    
    public function exchangeArray($data)
    {
        $this->code = (isset($data['code'])) ? $data['code'] : null;
        $this->label = (isset($data['label'])) ? $data['label'] : null;
    }
}

    
    
    
    
    
    