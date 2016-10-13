<?php
/**
 * Created by PhpStorm.
 * User: rco
 * Date: 20/06/14
 * Time: 10:48
 */

namespace Application\Form;

use Application\Form\CustomForm;
use Zend\Form\Element;


class LotForm extends CustomForm{


    public function initForm(){

        $this->add(array(
            'name' => $this->_name_prefix . 'lot_id',
            'type'  => 'hidden',
            'attributes'=>array('class'=>'form-control', 'style' => 'float: left;')
        ));

        $this->add(array(
            'name' => $this->_name_prefix . 'title',
            'options' => array(
                'label' => 'Titre',
                "required" => true
            ),
            'type'  => 'Text',
            'attributes'=>array('id' => 'lot_form_title', 'class'=>'form-control form_input', "required" => true),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'title_en',
            'options' => array(
                'label' => 'Titre(anglais)',
                "required" => true
            ),
            'type'  => 'Text',
            'attributes'=>array('id' => 'lot_form_title_en', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'description',
            'options' => array(
                'label' => 'Description',
            ),
            'type'  => 'Textarea',
            'attributes'=>array('id' => 'lot_form_desc', 'class'=>'form-control form_input'),
        ));
        
        $this->add(array(
            'name' => $this->_name_prefix . 'description_en',
            'options' => array(
                'label' => 'Description(anglais)',
            ),
            'type'  => 'Textarea',
            'attributes'=>array('id' => 'lot_form_desc_en', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'estimated_price',
            'options' => array(
                'label' => 'Prix estimé',
            ),
            'type'  => 'Text',
            'attributes'=>array('id' => 'lot_form_estimated_price', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'min_price',
            'options' => array(
                'label' => 'Prix de départ',
            ),
            'type'  => 'Text',
            'attributes'=>array('id' => 'lot_form_min_price', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'reserve_price',
            'options' => array(
                'label' => 'Prix de reserve',
            ),
            'type'  => 'Text',
            'attributes'=>array('id' => 'lot_form_reserve_price', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'ist_price',
            'options' => array(
                'label' => 'Pas d\'enchère',
            ),
            'type'  => 'Text',
            'attributes'=>array('id' => 'lot_form_ist_price', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'pre_auth_price',
            'options' => array(
                'label' => 'Montant de la pré-autorisation',
            ),
            'type'  => 'Text',
            'attributes'=>array('id' => 'lot_form_pre_auth_price', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'number',
            'options' => array(
                'label' => 'Tri',
            ),
            'type'  => 'Text',
            'attributes'=>array('id' => 'lot_form_number', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'video_link',
            'options' => array(
                'label' => 'Lien video',
            ),
            'type'  => 'Url',
            'attributes'=>array('id' => 'lot_form_video_link', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'cheval_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Cheval',
                'value_options' => $this->getActiveHorse()
            ),
            'attributes'=>array('id' => 'lot_form_cheval_id', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'vendeur_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Vendeur',
                'value_options' => $this->getActiveVendeur()
            ),
            'attributes'=>array('id' => 'lot_form_vendeur_id', 'class'=>'form-control form_input'),
        ));
        
        $this->add(array(
            'name' => $this->_name_prefix . 'image_url',
            'type' => 'Zend\Form\Element\File',
            'options' => array(
                'label' => 'Photo',
            ),
            'attributes'=>array('id' => 'cheval_form_image', 'class'=>'form-control form_input'),
        )); 
        
        $this->add(array(
            'name' => $this->_name_prefix . 'status',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Statut',
                'value_options' => array(
                    '0' => 'Inactif',
                    '1' => 'Actif',
                ),
            ),
            'attributes'=>array('id' => 'lot_form_status', 'class'=>'form-control form_input'),
        ));
    }
    
    public function getDefaultOptions(array $options)
    {
       $collectionConstraint = new \Symfony\Component\Validator\Constraints\Collection(array(
            'image_url' => new \Symfony\Component\Validator\Constraints\Image()
            ));

        $options["validation_constraint"] = $collectionConstraint;
        return $options;
    }
}
