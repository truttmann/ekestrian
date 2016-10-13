<?php
namespace RepriseBatch\Model;

class GoodPlan
{
    public $id;
    public $department_id;
    public $camping_id;
    public $partner_id;
    public $title_origin;
    public $content_origin;
    public $ordering;
    public $date_start;
    public $date_end;

    public function __construct(){}

    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->department_id = (isset($data ['department_id'])) ? $data['department_id'] : null;
        $this->camping_id = (isset($data ['camping_id'])) ? $data['camping_id'] : null;
        $this->partner_id = (isset($data ['partner_id'])) ? $data['partner_id'] : null;
        $this->title_origin = (isset($data ['title_origin'])) ? $data['title_origin'] : null;
        $this->content_origin = (isset($data ['content_origin'])) ? $data['content_origin'] : null;
        $this->ordering = (isset($data ['ordering'])) ? $data['ordering'] : null;
        $this->date_start = (isset($data ['date_start'])) ? $data['date_start'] : null;
        $this->date_end = (isset($data ['date_end'])) ? $data['date_end'] : null;
    }
}

    
    
    
    
    
