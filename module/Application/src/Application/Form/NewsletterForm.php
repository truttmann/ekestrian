<?php
/**
 * Created by PhpStorm.
 * User: rco
 * Date: 20/06/14
 * Time: 10:48
 */

namespace Application\Form;

use Application\Form\CustomForm;


class NewsletterForm extends CustomForm{


    public function initForm(){

        $this->add(array(
            'name' => $this->_name_prefix . 'newsletter_id',
            'type'  => 'hidden',
            'attributes'=>array('id' => 'newsletter_form_id', 'class'=>'form-control form_input')
        ));

        $this->add(array(
            'name' => $this->_name_prefix . 'email',
            'options' => array(
                'label' => 'Email',
            ),
            'type'  => 'Zend\Form\Element\Email',
            'attributes'=>array('id' => 'newsletter_form_email', 'class'=>'form-control form_input'),
        ));
    }
}
