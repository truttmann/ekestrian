<?php
namespace Application\Model;


class Image
{
    

    public function __construct(){}

    public function exchangeArray($data)
    {
        $ks = array_keys($data);
        foreach($ks as $k) {
            $this->$k = $data[$k];
        }
    }
}

    
    
    
    
    
