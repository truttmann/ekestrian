<?php
namespace Frontoffice;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\Request as ConsoleRequest;
use Zend\ServiceManager\ServiceManager;
use Zend\Filter\Word\UnderscoreToCamelCase;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\ModuleManager\Feature\FormElementProviderInterface;

class Module implements FormElementProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    // Gestion de l acces a la base de donnees pour toutes les entities
    public function getServiceConfig()
    {
        $factories['Zend\Session\SessionManager'] = function ($sm) {
            $config = $sm->get('config');
            if (isset($config['session'])) {
                $session = $config['session'];

                $sessionConfig = null;
                if (isset($session['config'])) {
                    $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                    $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                    $sessionConfig = new $class();
                    $sessionConfig->setOptions($options);
                }

                $sessionStorage = null;
                if (isset($session['storage'])) {
                    $class = $session['storage'];
                    $sessionStorage = new $class();
                }

                $sessionSaveHandler = null;
                if (isset($session['save_handler'])) {
                    // class should be fetched from service manager since it will require constructor arguments
                    $sessionSaveHandler = $sm->get($session['save_handler']);
                }

                $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                if (isset($session['validator'])) {
                    $chain = $sessionManager->getValidatorChain();
                    foreach ($session['validator'] as $validator) {
                        $validator = new $validator();
                        $chain->attach('session.validate', array($validator, 'isValid'));

                    }
                }
            } else {
                $sessionManager = new SessionManager();
            }
            Container::setDefaultManager($sessionManager);
            return $sessionManager;
        };

        return array('factories' => $factories);
    }

    public function onBootstrap($e)
    {
        $application 			= $e->getApplication();
        $sm 					= $application->getServiceManager();
        $translator 			= $sm->get('translator');
        $eventManager        	= $application->getEventManager();
        $moduleRouteListener 	= new \Zend\Mvc\ModuleRouteListener();
        $request = $e->getRequest();

        $config = $sm->get('Config');
        if(!array_key_exists("site", $config)) {
            throw new \Exception("Vous devez spécifiez l'url du site dans le fichier local");
        }
        if(!defined("URL_FRONT"))define("URL_FRONT", $config['site']['url']);
        
        /* Init sessions */
        $this->initializeSession($e);
        
        // On vérifie les droits ACL si on est pas en console
        $moduleRouteListener->attach($eventManager);
        /*if (!$request instanceof ConsoleRequest) {*/
            $eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'authPreDispatch'),1);
        /*}*/

        $this->loadConstant();
    }

    public function initializeSession($em)
    {
        $config = $em->getApplication()
                      ->getServiceManager()
                      ->get('Config');

        $sessionConfig = new SessionConfig();

        if($config && isset($config['session']))
            $sessionConfig->setOptions($config['session']);

        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->start();

        Container::setDefaultManager($sessionManager);
    }
    
    public function authPreDispatch($e) {
        try{
            $application   = $e->getApplication();
            $sm            = $application->getServiceManager();
         
            $router = $sm->get('router');
            $request = $sm->get('request');
            $routeMatch = $router->match($request);

            if(preg_match("/^Application.*$/", $routeMatch->getParam('controller')) && !$sm->get('user_service')->hasUserAccess()){
                $url = $e->getRouter ()->assemble ( array (
                    "controller" => "Application\Controller\Login" 
                ), array (
                    'name' => 'login' 
                ) );
                $response = $e->getResponse();
                $response->setHeaders ( $response->getHeaders ()->addHeaderLine ( 'Location', $url ));
                $response->setStatusCode ( 302 );
                $response->sendHeaders ();
                exit ();
            }
            /*if(!$sm->get('user_service')->hasUserAccess()){
                $url = $e->getRouter ()->assemble ( array (
                    "controller" => "Application\Controller\Login" 
                ), array (
                    'name' => 'login' 
                ) );
                $response = $e->getResponse();
                $response->setHeaders ( $response->getHeaders ()->addHeaderLine ( 'Location', $url ));
                $response->setStatusCode ( 302 );
                $response->sendHeaders ();
                exit ();
            }
            if(defined("IS_MAINTENANCE") && IS_MAINTENANCE === true) {
                $router = $sm->get('router');
                $request = $sm->get('request');
                $routeMatch = $router->match($request);
                if($routeMatch->getMatchedRouteName() != 'maintenance') {
                    $e->getTarget()->plugin('redirect')->toRoute('maintenance');
                }
            }*/
        } catch(\Exception $ex) {
            session_destroy();
            $response->getHeaders()->addHeaderLine('Location', $e->getRequest()->getBaseUrl() . '/404');
            $response->setStatusCode(404);
        }
    }
    // function qui va
    public function loadConstant()
    {
        if(file_exists(__DIR__."/config/constants.php")) {
            require_once(__DIR__."/config/constants.php");
        }
        if(file_exists(dirname(dirname(__DIR__))."/config/constants.php")) {
            require_once(__DIR__."/config/constants.php");
        }
    }

    public function getFormElementConfig()
    {
        return array(
            'invokables' => array(
                'uploadfield'     => 'Core\Form\UploadField',
            ),
        );
    }

}
