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

class NewsletterController extends InitController
{
    public function __construct($service)
    {
        $this->_service_locator = $service;
        parent::__construct();
    }

    public function addAction(){
        $newsModel = $this->getServiceLocator()->get('newsletterTable');
        
        if(array_key_exists("email", $_POST) && !empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
            $obj = new \Application\Model\Newsletter();
            $obj->email = $_POST['email'];
            $newsModel->save($obj);
        }
        echo "OK";
        exit;
    }
}
