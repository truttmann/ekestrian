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


class TranslateForm extends CustomForm{


    public function initForm(){

        $this->add(array(
            'name' => $this->_name_prefix . 'translate_id',
            'type'  => 'hidden',
            'attributes'=>array('class'=>'form-control', 'style' => 'float: left;')
        ));

        $this->add(array(
            'name' => $this->_name_prefix . 'code',
            'options' => array(
                'label' => 'Code',
                "required" => true,
                "readOnly" => true
            ),
            'type'  => 'Textarea',
            'attributes'=>array("readOnly" => true, 'id' => 'translate_form_code', 'class'=>'form-control form_input', "required" => true),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'libelle',
            'options' => array(
                'label' => 'Langue',
                "required" => true,
                "readOnly" => true
            ),
            'type'  => 'Textarea',
            'attributes'=>array("readOnly" => true, 'id' => 'translate_form_libelle', 'class'=>'form-control form_input', "required" => true),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'value',
            'options' => array(
                'label' => 'Valeur',
            ),
            'type'  => 'Textarea',
            'attributes'=>array('id' => 'translate_form_valeur', 'class'=>'form-control form_input'),
        ));
    }
    
    public function getDefaultOptions(array $options)
    {
       
    }
}
