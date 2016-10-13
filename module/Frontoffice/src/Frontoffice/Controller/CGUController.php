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

class CGUController extends InitController
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
        $viewModel->setTemplate('frontoffice/cgu');
        return $viewModel;
    }
}
