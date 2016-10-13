<?php
namespace RepriseBatch\Model;

class Country
{
    public $id;
    public $iso;
    public $reference;
    public $label_origin;

    public function __construct(){}

    public function exchangeArray($data)
    {
        $this->id     = (isset($data['id'])) ? $data['id'] : null;
        $this->label_origin = (isset($data['label_origin'])) ? $data['label_origin'] : null;
        $this->iso = (isset($data['iso'])) ? $data['iso'] : null;
        $this->reference = (isset($data['reference'])) ? $data['reference'] : null;
    }

    /**
    * check is this object is different that parameter
    * @param  Country $obj object for diff
    * @return bool
    */
    public function isDiff(Country $obj) {
        $return = false;
        $return = ($this->id != $obj->id) ? true: $return;
        $return = ($this->iso != $obj->iso) ? true: $return;
        $return = ($this->reference != $obj->reference) ? true: $return;
        $return = ($this->label_origin != $obj->label_origin) ? true: $return;
        return $return;
    }
}