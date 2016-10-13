<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Frontoffice\Controller;


use Zend\View\Model\ViewModel;
use Frontoffice\Controller\InitController;

class IndexController extends InitController
{
    public function __construct($service)
    {
        $this->_service_locator = $service;
        parent::__construct();
    }

    public function indexAction(){
        parent::initListJs();
        

        $viewModel = new ViewModel();
        $viewModel->setVariable("lang_id", $this->lang_id);
        $viewModel->setTemplate('frontoffice/accueil');
        $viewModel->setVariable("lang_id", $this->lang_id);
        
        $selection = new ViewModel();
        $selection->setVariable("lang_id", $this->lang_id);
        $selection->setTemplate('frontoffice/page/selection');
        $selection->setVariable("lang_id", $this->lang_id);
        $viewModel->addChild($selection, 'selection');
        
        $purchase = new ViewModel();
        $purchase->setVariable("lang_id", $this->lang_id);
        $purchase->setTemplate('frontoffice/page/purchase');
        $purchase->setVariable("lang_id", $this->lang_id);
        $viewModel->addChild($purchase, 'purchase');
        
        $services = new ViewModel();
        $services->setVariable("lang_id", $this->lang_id);
        $services->setTemplate('frontoffice/page/services');
        $services->setVariable("lang_id", $this->lang_id);
        $viewModel->addChild($services, 'services');
        
        /* Liste des enchères actives */
        $t = array();
        $r = $this->_service_locator->get('enchereTable')->fetchAll(array("status" => 1));
        foreach ($r as $i) {
            $t[] = $i;
        }
        $viewModel->setVariable("encheres", $t);
        return $viewModel;
    }
}
