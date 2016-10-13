<?php
namespace RepriseBatch\Model;

class Camping
{
    public $id;
    public $city_id;
    public $reference;
    public $name;
    public $zip_code;
    public $access;
    public $address;
    public $address2;
    // public $url_default;
    public $media_id;
    public $nbr_places;
    public $_short_description;
    public $_long_description;
    public $_meta_description;
    public $is_logos_accepted = 1;
    public $_is_favorite = 0;
    public $_is_our_selection = 0;
    public $_priority = 99;
    // public $phone_number;
    // public $phone_number2;
    // public $fax;
    public $email;
//    public $price_list_year;
    public $rating;
    public $coord_latitude;
    public $coord_longitude;
    public $status = 1;
    public $created_at;
    public $updated_at;

    public function __construct(){}

    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->reference = (isset($data['reference'])) ? $data['reference'] : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->access = (isset($data['access'])) ? $data['access'] : null;
        $this->address = (isset($data['address'])) ? $data['address'] : null;
        $this->address2 = (isset($data['address2'])) ? $data['address2'] : null;
        // $this->url_default = (isset($data['url_default'])) ? $data['url_default'] : null;
        $this->media_id = (isset($data['media_id'])) ? $data['media_id'] : null;
        $this->nbr_places = (isset($data['nbr_places'])) ? $data['nbr_places'] : null;
        $this->is_logos_accepted = (isset($data['is_logos_accepted'])) ? $data['is_logos_accepted'] : 1;
        $this->_is_favorite = (isset($data['_is_favorite'])) ? $data['_is_favorite'] : 0;
        $this->_is_our_selection = (isset($data['_is_our_selection'])) ? $data['_is_our_selection'] : 0;
        $this->_priority = (isset($data['_priority'])) ? $data['_priority'] : 99;
        $this->zip_code = (isset($data['zip_code'])) ? $data['zip_code'] : null;
        // $this->phone_number = (isset($data['phone_number'])) ? $data['phone_number'] : null;
        // $this->phone_number2 = (isset($data['phone_number2'])) ? $data['phone_number2'] : null;
        // $this->fax = (isset($data['fax'])) ? $data['fax'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
//        $this->price_list_year = (isset($data['price_list_year'])) ? $data['price_list_year'] : null;
        $this->rating = (isset($data['rating'])) ? $data['rating'] : null;
        $this->coord_latitude = (isset($data['coord_latitude'])) ? $data['coord_latitude'] : null;
        $this->coord_longitude = (isset($data['coord_longitude'])) ? $data['coord_longitude'] : null;
        $this->city_id = (isset($data['city_id'])) ? $data['city_id'] : null;
        $this->status = (isset($data['status'])) ? $data['status'] : 1;
        $this->created_at = (isset($data['created_at'])) ? $data['created_at'] : null;
        $this->updated_at = (isset($data['updated_at'])) ? $data['updated_at'] : null;
    }

    /**
    * check is this object is different that parameter
    * @param  Camping $obj object for diff
    * @return bool
    */
    public function isDiff(Camping $obj) {
        $return = false;
        $return = ($this->id != $obj->id) ? true : $return;
        $return = ($this->reference != $obj->reference) ? true : $return;
        $return = ($this->name != $obj->name) ? true : $return;
        $return = ($this->access != $obj->access) ? true : $return;
        $return = ($this->address != $obj->address) ? true : $return;
        $return = ($this->address2 != $obj->address2) ? true : $return;
        $return = ($this->zip_code != $obj->zip_code) ? true : $return;
        // $return = ($this->phone_number != $obj->phone_number) ? true : $return;
        // $return = ($this->phone_number2 != $obj->phone_number2) ? true : $return;
        // $return = ($this->fax != $obj->fax) ? true : $return;
        $return = ($this->email != $obj->email) ? true : $return;
//        $return = ($this->price_list_year != $obj->price_list_year) ? true : $return;
        $return = ($this->rating != $obj->rating) ? true : $return;
        $return = ($this->coord_latitude != $obj->coord_latitude) ? true : $return;
        $return = ($this->coord_longitude != $obj->coord_longitude) ? true : $return;
        $return = ($this->city_id != $obj->city_id) ? true : $return;
        $return = ($this->status != $obj->status) ? true : $return;
        return $return;

    }
}

    
    
    
    
    
