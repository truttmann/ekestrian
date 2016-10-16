<?php
namespace Application\Model;

class User
{
     public $id;
     public $login;
     public $password;
     public $name;
     public $created_at;
     public $updated_at;

    public function __construct(){}

    public function exchangeArray($data)
    {
        $ks = array_keys($data);
        foreach($ks as $k) {
            $this->$k = $data[$k];
        }
    }
}

    
    
    
    
    
