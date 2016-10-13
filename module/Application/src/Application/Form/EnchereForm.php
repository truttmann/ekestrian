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


class EnchereForm extends CustomForm{


    public function initForm(){

        $this->add(array(
            'name' => $this->_name_prefix . 'enchere_id',
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
            'attributes'=>array('id' => 'enchere_form_title', 'class'=>'form-control form_input', "required" => true),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'title_en',
            'options' => array(
                'label' => 'Titre(anglais)',
                "required" => true
            ),
            'type'  => 'Text',
            'attributes'=>array('id' => 'enchere_form_title_en', 'class'=>'form-control form_input', "required" => true),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'description',
            'options' => array(
                'label' => 'Description',
            ),
            'type'  => 'Textarea',
            'attributes'=>array('id' => 'enchere_form_desc', 'class'=>'form-control form_input'),
        ));
        
        $this->add(array(
            'name' => $this->_name_prefix . 'description_en',
            'options' => array(
                'label' => 'Description(anglais)',
            ),
            'type'  => 'Textarea',
            'attributes'=>array('id' => 'enchere_form_desc_en', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'start_date',
            'options' => array(
                'label' => 'Début de la vente',
                'format' => 'Y-m-d\TH:iP'
            ),
            'type'  => 'Zend\Form\Element\DateTimeLocal',
            'attributes'=>array(
                'id' => 'enchere_form_start_date', 
                'class'=>'form-control form_input',
                'min' => '2010-01-01T00:00+02:00',
                'max' => '2020-01-01T00:00+02:00',
            ),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'end_date',
            'options' => array(
                'label' => 'Fin de la vente',
                'format' => 'Y-m-d\TH:iP'
            ),
            'type'  => '\Zend\Form\Element\DateTimeLocal',
            'attributes'=>array(
                'id' => 'enchere_form_end_date', 
                'class'=>'form-control form_input',
                'min' => '2010-01-01T00:00+02:00',
                'max' => '2020-01-01T00:00+02:00',
            ),
        ));   
        
        $this->add(array(
            'name' => $this->_name_prefix . 'image_url',
            'type' => 'Zend\Form\Element\File',
            'options' => array(
                'label' => 'Photo',
            ),
            'attributes'=>array('id' => 'enchere_form_image', 'class'=>'form-control form_input'),
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
            'attributes'=>array('id' => 'enchere_form_status', 'class'=>'form-control form_input'),
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
