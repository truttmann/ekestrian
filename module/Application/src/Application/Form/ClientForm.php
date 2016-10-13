<?php
/**
 * Created by PhpStorm.
 * User: rco
 * Date: 20/06/14
 * Time: 10:48
 */

namespace Application\Form;

use Application\Form\CustomForm;


class ClientForm extends CustomForm{


    public function initForm(){

        $this->add(array(
            'name' => $this->_name_prefix . 'client_id',
            'type'  => 'hidden',
            'attributes'=>array('class'=>'form-control')
        ));

        $this->add(array(
            'name' => $this->_name_prefix . 'firstname',
            'options' => array(
                'label' => 'Nom',
            ),
            'type'  => 'Text',
            'attributes'=>array('required' => true, 'id'=>'client_form_firstname' ,'class'=>'form-control form_input'),
        ));
        
        $this->add(array(
            'name' => $this->_name_prefix . 'lastname',
            'options' => array(
                'label' => 'Prénom',
            ),
            'type'  => 'Text',
            'attributes'=>array('required' => true,'id'=>'client_form_lastname' ,'class'=>'form-control form_input'),
        ));
        
        $this->add(array(
            'name' => $this->_name_prefix . 'civility',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Civilité',
                'value_options' => array(
                    'mme' => 'Mrs',
                    'mr' => 'Mme',
                ),
            ),
           'attributes'=>array('required' => true,'id'=>'client_form_civility' ,'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'country_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Pays',
                'value_options' => $this->getCountries()
            ),
            'attributes'=>array('id'=>'client_form_country' ,'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'societe',
            'options' => array(
                'label' => 'Société',
            ),
            'type'  => 'Text',
            'attributes'=>array('id'=>'client_form_societe' ,'class'=>'form-control form_input'),
        ));


        $this->add(array(
            'name' => $this->_name_prefix . 'email',
            'options' => array(
                'label' => 'Email',
            ),
            'type' => 'Zend\Form\Element\Email',
            'attributes'=>array('id'=>'client_form_email' ,'class'=>'form-control form_input'),
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
            'attributes'=>array('id'=>'client_form_status' ,'class'=>'form-control form_input'),
        ));
    }
}
