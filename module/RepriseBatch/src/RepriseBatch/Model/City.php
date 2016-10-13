<?php
namespace RepriseBatch\Model;

class City
{
    public $id;
    public $department_id;
    public $reference;
    public $insee;
    public $label_origin;
    public $situation_origin;
    public $coord_latitude;
    public $coord_longitude;

    public function __construct(){}
    
    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->department_id = (isset($data['department_id'])) ? $data['department_id'] : null;
        $this->reference = (isset($data['reference'])) ? $data['reference'] : null;
        $this->insee = (isset($data['insee'])) ? $data['insee'] : null;
        $this->situation_origin = (isset($data['situation_origin'])) ? $data['situation_origin'] : null;
        $this->label_origin = (isset($data['label_origin'])) ? $data['label_origin'] : null;
        $this->coord_latitude = (isset($data['coord_latitude'])) ? $data['coord_latitude'] : null;
        $this->coord_longitude = (isset($data['coord_longitude'])) ? $data['coord_longitude'] : null;
    }

    /**
    * check is this object is different that parameter
    * @param  Region $obj object for diff
    * @return bool
    */
    public function isDiff(City $obj) {
        $return = false;
        $return = ($this->id != $obj->id )  ? true : $return;
        $return = ($this->department_id != $obj->department_id )  ? true : $return;
        $return = ($this->reference != $obj->reference )  ? true : $return;
        $return = ($this->insee != $obj->insee )  ? true : $return;
        $return = ($this->situation_origin != $obj->situation_origin )  ? true : $return;
        $return = ($this->label_origin != $obj->label_origin )  ? true : $return;
        $return = ($this->coord_latitude != $obj->coord_latitude )  ? true : $return;
        $return = ($this->coord_longitude != $obj->coord_longitude )  ? true : $return;
        return $return;
    }
}

    
    
    
    
    
    