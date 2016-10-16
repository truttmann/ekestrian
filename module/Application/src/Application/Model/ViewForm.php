<?php
/**
 * Created by PhpStorm.
 * User: flc
 * Date: 19/06/14
 * Time: 17:07
 */

namespace Application\Model;

use Zend\View\Model\ViewModel;

class ViewForm extends ViewModel {

    protected $_allowed_buttons = array(
        'back',
        'save',
        'options'
    );

    public function __construct($title, $back_route, $action_route, $allowed_buttons = array(), $history_info = array()){

        if(!empty($allowed_buttons))
            $this->_allowed_buttons = $allowed_buttons;

        $this->setVariable('allowed_buttons', $this->_allowed_buttons);
        $this->setTemplate('application/page/form_container');
        
        $this->setVariable('label_save_button', 'Sauvegarder');
        $this->setVariable('label_back_button', 'Retour');

    }
} 