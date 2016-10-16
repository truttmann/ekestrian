<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Frontoffice\Form;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\View\Model\ViewModel;

class CustomForm extends Form{

    protected $_name_prefix = '';
    protected $_template = '';
    protected $_service_locator = null;

    public function setNamePrefix($prefix){
        $this->_name_prefix = $prefix;
        return $this;
    }

    public function getNamePrefix(){
        return $this->_name_prefix;
    }

    public function toHtml(){
        $viewModel = new ViewModel();
        $viewModel->setTemplate($this->_template);

        $items = $this->getItems();
        $viewModel->setVariables(array('items' => $items));



        return $viewModel;
    }

    protected function getCountries(){
        $items  = $this->getServiceLocator()->get('countryTable')->fetchAllPaginate(array(), 'libelle');
        $items->setDefaultItemCountPerPage($items->getTotalItemCount());
        $return  = array();
        foreach($items as $item){
            $return[$item->country_id] = $item->libelle;
        }
        return $return;
    }
    

    protected function formatSelect($data){
        $return  = array();
        foreach($data as $item){
            $return[$item->id] = $item->label_origin;
        }

        return $return;
    }

    public function setServiceLocator($sl){
        if($this->_service_locator === null){
            $this->_service_locator = $sl;
        }
        return $this;
    }

    public function getServiceLocator(){
        if($this->_service_locator === null){

            throw new \Exception('Sl empty.');
        }
        return $this->_service_locator;
    }
}

