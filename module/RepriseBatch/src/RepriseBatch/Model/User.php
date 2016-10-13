<?php
namespace RepriseBatch\Model;

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
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->login = (isset($data['login'])) ? $data['login'] : null;
        $this->password = (isset($data['password'])) ? $data['password'] : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->created_at = (isset($data['created_at'])) ? $data['created_at'] : null;
        $this->updated_at = (isset($data['updated_at'])) ? $data['updated_at'] : null;
    }
}

    
    
    
    
    
