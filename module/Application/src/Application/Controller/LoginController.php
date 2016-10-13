<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;


class LoginController extends InitController
{

    public function loginAction(){
        $viewModel = new ViewModel();
        $viewModel->setTemplate('application/login');
        $viewModel->setTerminal(true);
        return $viewModel;

    }

    public function saveloginAction(){


        try{
            if(! array_key_exists("username", $_POST) || ! array_key_exists("password", $_POST)) {
                throw new \Exception("Invalid login or password");                
            }
            
            /* verification de la bonne authentification en base */
            $login = $this->getRequest()->getPost('username');
            $password = $this->getRequest()->getPost('password');

            
            if(empty($login) || empty($password)) {
                return $this->_setRetour('login', 'error' , "Veuillez bien renseigner le login et le mot de passe");
            }

            // authentification
            $userFactory = $this->getServiceLocator()->get('userTable');
            $user_log = $userFactory->getByLoginPass($login, hash("sha1", $password));

            $session = $this->getServiceLocator()->get('user_service')->setLoggedUser($user_log);

            return $this->redirect()->toRoute('home_back');
        } catch(\Exception $e) {
            $this->addWarning($e->getMessage());
            $viewModel = new ViewModel();
            $viewModel->setTemplate('application/login');
            $viewModel->setTerminal(true);
            return $viewModel;
        }

    }

    public function logoutAction(){
        $auth = new AuthenticationService();
        $auth->clearIdentity();
        $this->getServiceLocator()->get("user_service")->clearSession();
        session_destroy();
        $this->redirect()->toRoute('login');
    }

}
