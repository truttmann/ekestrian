<?php
/**
 * Created by PhpStorm.
 * User: rco
 * Date: 20/06/14
 * Time: 10:48
 */

namespace Frontoffice\Form;

use Frontoffice\Form\CustomForm;


class MembreForm extends CustomForm{


    public function initForm(){

        $this->add(array(
            'name' => $this->_name_prefix . 'client_id',
            'type'  => 'hidden',
            'attributes'=>array('class'=>'form-control')
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'civility',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => '*Civilité',
                'value_options' => array(
                    'mme' => 'Mr',
                    'mr' => 'Mme',
                ),
            ),
           'attributes'=>array('required' => true,'id'=>'client_form_civility' ,'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'lastname',
            'options' => array(
                'label' => '*Prénom',
            ),
            'type'  => 'Text',
            'attributes'=>array('required' => true,'id'=>'client_form_lastname' ,'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'firstname',
            'options' => array(
                'label' => '*Nom',
            ),
            'type'  => 'Text',
            'attributes'=>array('required' => true, 'id'=>'client_form_firstname' ,'class'=>'form-control form_input'),
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
            'name' => $this->_name_prefix . 'phone_ind',
            'type'  => 'Select',
            'options' => array(
                'label' => '*Téléphone',
                'value_options' => array(
                    '+33' => '+33',
                ),
            ),
            'attributes'=>array('required' => true,'id'=>'client_form_phone_ind' ,'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'phone',
            'type'  => 'Text',
            'attributes'=>array('required' => true,'id'=>'client_form_phone' ,'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'birthday',
            'options' => array(
                'label' => '*Date de naissance',
            ),
            'type'  => 'Date',
            'attributes'=>array('required' => true,'id' => 'cheval_form_race', 'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'country_id',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => '*Nationalité',
                'value_options' => $this->getCountries()
            ),
            'attributes'=>array('required' => true,'id'=>'client_form_country' ,'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'langue',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Langue',
                'value_options' => array(
                    "francais" => "Français",
                    "anglais" => "Anglais",
                )
            ),
            'attributes'=>array('required' => true,'id'=>'client_form_country' ,'class'=>'form-control form_input'),
        ));       
        $this->add(array(
            'name' => $this->_name_prefix . 'type',
            'type' => 'Zend\Form\Element\Radio',
            'options' => array(
                'label' => '*Statut',
                'value_options' => array(
                    array(
                        'value' => 'part',
                        'label' => 'Particulier',
                        'selected' => true,
                        'disabled' => false,
                        'attributes' => array(
                            'id' => 'part_check',
                        )
                    ),
                    array(
                        'value' => 'pro',
                        'label' => 'Professionel',
                        'selected' => false,
                        'attributes' => array(
                            'id' => 'pro_check',
                        )
                    ),
                ),
            ),
            'attributes'=>array('required' => true,'id'=>'client_form_type' ,'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'email',
            'options' => array(
                'label' => '*Email',
            ),
            'type' => 'Zend\Form\Element\Email',
            'attributes'=>array('required' => true,'id'=>'client_form_email' ,'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'password',
            'options' => array(
                'label' => '*Password',
            ),
            'type' => 'Zend\Form\Element\Password',
            'attributes'=>array('required' => true,'id'=>'client_form_paswword' ,'class'=>'form-control form_input'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'password_confirm',
            'options' => array(
                'label' => '*Confirmation',
            ),
            'type' => 'Zend\Form\Element\Password',
            'attributes'=>array('required' => true,'id'=>'client_form_password_confirm' ,'class'=>'form-control form_input'),
        )); 
        $this->add(array(
            'name' => $this->_name_prefix . 'condition_vente',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes'=>array('required' => true,'id'=>'client_form_condition_vente' ,'class'=>'form-control form_input input_checkbox'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'reglement',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes'=>array('required' => true,'id'=>'client_form_reglement' ,'class'=>'form-control form_input input_checkbox'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'confidence',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes'=>array('required' => true,'id'=>'client_form_confidence' ,'class'=>'form-control form_input input_checkbox'),
        ));
        $this->add(array(
            'name' => $this->_name_prefix . 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'value' => 'Valider',
            'attributes' => array(
                'value' => 'Valider',
                'id' => 'submitbutton',
            ),
        ));
    }
}
