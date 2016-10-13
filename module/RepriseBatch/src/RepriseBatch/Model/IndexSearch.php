<?php
namespace RepriseBatch\Model;

class IndexSearch
{
    public $keyword;
    public $label;
    public $table_name;
    public $data_id;
    public $language_code;

    public function __construct(){}
    
    public function exchangeArray($data)
    {
        $this->keyword = (isset($data['keyword'])) ? $data['keyword'] : null;
        $this->label = (isset($data['label'])) ? $data['label'] : null;
        $this->table_name = (isset($data['table_name'])) ? $data['table_name'] : null;
        $this->data_id = (isset($data['data_id'])) ? $data['data_id'] : null;
        $this->code = (isset($data['code'])) ? $data['code'] : null;
        $this->code = (isset($data['language_code'])) ? $data['language_code'] : null;
    }
}

    
    
    
    
    
    