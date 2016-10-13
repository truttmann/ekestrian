<?php

namespace RepriseBatch\Model;

class FlatCamping
{
    public $campingId;
    public $logoChaineId;
    public $isOurSelection;
    public $isFavorite;
    public $priority;
    public $geoOrder;
    public $coordLatitude;
    public $coordLongitude;
    public $cityCoordLatitude;
    public $cityCoordLongitude;
    public $cityId;
    public $departmentId;
    public $regionId;

    /**
     * Liste des filtres sous la forme : array(columnName, value)
     * Ex : array(
     *     'filter_19' => 1,
     *     'filter_54' => null
     * )
     * @var array
     */
    public $filters;

    /**
     * Même chose que les filtres
     * @var array
     */
    public $thematics;

    public function __construct(){}

    public function exchangeArray($data)
    {
        $this->campingId = (isset($data['camping_id'])) ? $data['camping_id'] : null;
        $this->logoChaineId = (isset($data['logo_chaine_id'])) ? $data['logo_chaine_id'] : null;
        $this->isOurSelection = (isset($data['_is_our_selection'])) ? $data['_is_our_selection'] : null;
        $this->isFavorite = (isset($data['_is_favorite'])) ? $data['_is_favorite'] : null;
        $this->priority = (isset($data['_priority'])) ? $data['_priority'] : null;
        $this->coordLatitude = (isset($data['coord_latitude'])) ? $data['coord_latitude'] : null;
        $this->coordLongitude = (isset($data['coord_longitude'])) ? $data['coord_longitude'] : null;
        $this->cityCoordLatitude = (isset($data['city_coord_latitude'])) ? $data['city_coord_latitude'] : null;
        $this->cityCoordLongitude = (isset($data['city_coord_longitude'])) ? $data['city_coord_longitude'] : null;
        $this->cityId = (isset($data['city_id'])) ? $data['city_id'] : null;
        $this->departmentId = (isset($data['department_id'])) ? $data['department_id'] : null;
        $this->regionId = (isset($data['region_id'])) ? $data['region_id'] : null;
        foreach ($data as $key => $value) {
            if (strstr($key, 'geo_order_')) {
                $this->$key = $value;
            }
        }
    }
}