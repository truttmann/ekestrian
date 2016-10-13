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


class ChevalForm extends CustomForm{


    public function initForm(){

        $this->add(array(
            'name' => $this->_name_prefix . 'cheval_id',
            'type'  => 'hidden',
            'attributes'=>array('class'=>'form-control', 'style' => 'float: left;')
        ));

        $this->add(array(
            'name' => $this->_name_prefix . 'name',
            'options' => array(
                'label' => 'Nom',
                "required" => true
            ),
            'type'  => 'Text',
            'attributes'=>array('id' => 'cheval_form_name', 'class'=>'form-control form_input', "required" => true),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'description',
            'options' => array(
                'label' => 'Description',
            ),
            'type'  => 'Textarea',
            'attributes'=>array('id' => 'cheval_form_desc', 'class'=>'form-control form_input'),
        ));
        
        $this->add(array(
            'name' => $this->_name_prefix . 'description_en',
            'options' => array(
                'label' => 'Description(anglais)',
            ),
            'type'  => 'Textarea',
            'attributes'=>array('id' => 'cheval_form_desc_en', 'class'=>'form-control form_input'),
        ));
        
        $this->add(array(
            'name' => $this->_name_prefix . 'quality',
            'options' => array(
                'label' => 'Qualité',
            ),
            'type'  => 'Text',
            'attributes'=>array('id' => 'cheval_form_quality', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'sex',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Sexe',
                'value_options' => array(
                    'm' => 'Mâle',
                    'f' => 'Femelle',
                    'e' => 'Embryon'
                ),
            ),
            'attributes'=>array('id' => 'cheval_form_sex', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'race',
            'options' => array(
                'label' => 'Race',
            ),
            'type'  => 'Text',
            'attributes'=>array('id' => 'cheval_form_race', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'birthday',
            'options' => array(
                'label' => 'Date de naissance',
            ),
            'type'  => 'Date',
            'attributes'=>array('id' => 'cheval_form_race', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'father_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Père',
                'value_options' => $this->getActiveHorseMale()
            ),
            'attributes'=>array('id' => 'cheval_form_father_id', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'mother_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Mère',
                'value_options' => $this->getActiveHorseFemale()
            ),
            'attributes'=>array('id' => 'cheval_form_mother_id', 'class'=>'form-control form_input'),
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
            'attributes'=>array('id' => 'cheval_form_status', 'class'=>'form-control form_input'),
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
