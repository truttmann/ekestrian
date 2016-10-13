<?php
namespace RepriseBatch\Model;

class CampingCaracteristic
{
     public $id;
     public $camping_id;
     public $caracteristic_id;
     public $group_caracteristics_id;
     public $ad;
     public $val;
     public $year;     
     public $distance;
     public $nom;
     public $tarif;
     public $tarif2;
     public $quantite;
     public $quantite2;
     public $remarque;
     public $duree;
     public $nbre_total;
     public $tourisme;
     public $loisirs;
     public $campingcar;
     public $tente;
     public $ouv1;
     public $ouv2;
     public $ouv3;
     public $ouv4;
     public $dates;

    public function __construct(){}

    public function exchangeArray($data)
    {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->camping_id = (isset($data['camping_id'])) ? $data['camping_id'] : null;
        $this->caracteristic_id = (isset($data['caracteristic_id'])) ? $data['caracteristic_id'] : null;
        $this->group_caracteristics_id = (isset($data['group_caracteristics_id'])) ? $data['group_caracteristics_id'] : null;
        $this->direct_access = (isset($data['direct_access'])) ? $data['direct_access'] : null;
        $this->value = (isset($data['value'])) ? $data['value'] : null;
        $this->year = (isset($data['year'])) ? $data['year'] : null;
        $this->distance = (isset($data['distance'])) ? $data['distance'] : null;
        $this->nom = (isset($data['nom'])) ? $data['nom'] : null;
        $this->tarif = (isset($data['tarif'])) ? $data['tarif'] : null;
        $this->tarif2 = (isset($data['tarif2'])) ? $data['tarif2'] : null;
        $this->quantite = (isset($data['quantite'])) ? $data['quantite'] : null;
        $this->quantite2 = (isset($data['quantite2'])) ? $data['quantite2'] : null;
        $this->remarque = (isset($data['remarque'])) ? $data['remarque'] : null;
        $this->duree = (isset($data['duree'])) ? $data['duree'] : null;
        $this->nbre_total = (isset($data['nbre_total'])) ? $data['nbre_total'] : null;
        $this->tourisme = (isset($data['tourisme'])) ? $data['tourisme'] : null;
        $this->loisirs = (isset($data['loisirs'])) ? $data['loisirs'] : null;
        $this->campingcar = (isset($data['campingcar'])) ? $data['campingcar'] : null;
        $this->tente = (isset($data['tente'])) ? $data['tente'] : null;
        $this->ouv1 = (isset($data['ouv1'])) ? $data['ouv1'] : null;
        $this->ouv2 = (isset($data['ouv2'])) ? $data['ouv2'] : null;
        $this->ouv3 = (isset($data['ouv3'])) ? $data['ouv3'] : null;
        $this->ouv4 = (isset($data['ouv4'])) ? $data['ouv4'] : null;
        $this->dates = (isset($data['dates'])) ? $data['dates'] : null;
    }
}