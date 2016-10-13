<?php
namespace RepriseBatch\Model;

class Region
{
    public $id;
    public $country_id;
    public $reference;
    public $insee;
    public $label_origin;
    public $coord_latitude;
    public $coord_longitude;
    public $coords_minimap;

    public function __construct(){}

    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->country_id = (isset($data['country_id'])) ? $data['country_id'] : null;
        $this->reference = (isset($data['reference'])) ? $data['reference'] : null;
        $this->insee = (isset($data['insee'])) ? $data['insee'] : null;
        $this->label_origin = (isset($data['label_origin'])) ? $data['label_origin'] : null;
        $this->coord_latitude = (isset($data['coord_latitude'])) ? $data['coord_latitude'] : null;
        $this->coord_longitude = (isset($data['coord_longitude'])) ? $data['coord_longitude'] : null;
        $this->coords_minimap = (isset($data['coords_minimap'])) ? $data['coords_minimap'] : null;
    }

    /**
    * check is this object is different that parameter
    * @param  Region $obj object for diff
    * @return bool
    */
    public function isDiff(Region $obj) {
        $return = false;
        $return = ($this->id != $obj->id) ? true: $return;
        $return = ($this->country_id != $obj->country_id) ? true: $return;
        $return = ($this->reference != $obj->reference) ? true: $return;
        $return = ($this->insee != $obj->insee) ? true: $return;
        $return = ($this->label_origin != $obj->label_origin) ? true: $return;
        $return = ($this->coord_latitude != $obj->coord_latitude) ? true: $return;
        $return = ($this->coord_longitude != $obj->coord_longitude) ? true: $return;
        $return = ($this->coords_minimap != $obj->coords_minimap) ? true: $return;
        return $return;
    }
}