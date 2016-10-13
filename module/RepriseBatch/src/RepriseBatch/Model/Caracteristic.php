<?php
namespace RepriseBatch\Model;

class Caracteristic
{

    public $id;
    public $group_caracteristics_id;
    public $suffix_xml_tag_year;
    public $xml_tag;
    public $label_origin;
    public $logo_path;
    public $logo_display;
    public $label_url;
    public $order;
    public $label_ad;

    public function __construct(){}

    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->group_caracteristics_id = (isset($data['group_caracteristics_id'])) ? $data['group_caracteristics_id'] : null;
        $this->suffix_xml_tag_year = (isset($data['suffix_xml_tag_year'])) ? $data['suffix_xml_tag_year'] : null;
        $this->xml_tag = (isset($data['xml_tag'])) ? $data['xml_tag'] : null;
        $this->label_origin = (isset($data['label_origin'])) ? $data['label_origin'] : null;
        $this->logo_path = (isset($data['logo_path'])) ? $data['logo_path'] : null;
        $this->logo_display = (isset($data['logo_display'])) ? $data['logo_display'] : null;
        $this->label_url = (isset($data['label_url'])) ? $data['label_url'] : null;
        $this->order = (isset($data['order'])) ? $data['order'] : null;
        $this->label_ad = (isset($data['label_ad'])) ? $data['label_ad'] : null;
    }
}