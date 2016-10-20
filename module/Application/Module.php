<?php
namespace Application;

use Application\Model\User;
use Application\Model\UserTable;
use Application\Model\Client;
use Application\Model\ClientTable;
use Application\Model\Country;
use Application\Model\CountryTable;
use Application\Model\Vendeur;
use Application\Model\VendeurTable;
use Application\Model\Cheval;
use Application\Model\ChevalTable;
use Application\Model\Enchere;
use Application\Model\EnchereTable;
use Application\Model\Image;
use Application\Model\ImageTable;
use Application\Model\Lot;
use Application\Model\LotTable;
use Application\Model\ClientAuction;
use Application\Model\ClientAuctionTable;
use Application\Model\Newsletter;
use Application\Model\NewsletterTable;
use Application\Model\Translate;
use Application\Model\TranslateTable;
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
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;

class Module implements FormElementProviderInterface, 
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ConsoleUsageProviderInterface
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
        $factories['userTable'] = function(ServiceManager $sm) {
            $tableGateway = $sm->get("userTableGateway");
            $table = new UserTable($tableGateway);
            return $table;
        };
        $factories["userTableGateway"] = function (ServiceManager $sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new User());
            return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
        };
        
        $factories['clientTable'] = function(ServiceManager $sm) {
            $tableGateway = $sm->get("clientTableGateway");
            $table = new ClientTable($tableGateway);
            return $table;
        };
        $factories["clientTableGateway"] = function (ServiceManager $sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Client());
            return new TableGateway('client', $dbAdapter, null, $resultSetPrototype);
        };
        
        $factories['countryTable'] = function(ServiceManager $sm) {
            $tableGateway = $sm->get("countryTableGateway");
            $table = new CountryTable($tableGateway);
            return $table;
        };
        $factories["countryTableGateway"] = function (ServiceManager $sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Country());
            return new TableGateway('country', $dbAdapter, null, $resultSetPrototype);
        };
        
        $factories['vendeurTable'] = function(ServiceManager $sm) {
            $tableGateway = $sm->get("vendeurTableGateway");
            $table = new VendeurTable($tableGateway);
            return $table;
        };
        $factories["vendeurTableGateway"] = function (ServiceManager $sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Vendeur());
            return new TableGateway('vendeur', $dbAdapter, null, $resultSetPrototype);
        };
        
        $factories['chevalTable'] = function(ServiceManager $sm) {
            $tableGateway = $sm->get("chevalTableGateway");
            $table = new ChevalTable($tableGateway);
            return $table;
        };
        $factories["chevalTableGateway"] = function (ServiceManager $sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Cheval());
            return new TableGateway('cheval', $dbAdapter, null, $resultSetPrototype);
        };
        
        $factories['enchereTable'] = function(ServiceManager $sm) {
            $tableGateway = $sm->get("enchereTableGateway");
            $table = new EnchereTable($tableGateway);
            return $table;
        };
        $factories["enchereTableGateway"] = function (ServiceManager $sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Enchere());
            return new TableGateway('enchere', $dbAdapter, null, $resultSetPrototype);
        };
        
        $factories['lotTable'] = function(ServiceManager $sm) {
            $tableGateway = $sm->get("lotTableGateway");
            $table = new LotTable($tableGateway);
            return $table;
        };
        $factories["lotTableGateway"] = function (ServiceManager $sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Lot());
            return new TableGateway('lot', $dbAdapter, null, $resultSetPrototype);
        };
        
        $factories['imageTable'] = function(ServiceManager $sm) {
            $tableGateway = $sm->get("imageTableGateway");
            $table = new ImageTable($tableGateway);
            return $table;
        };
        $factories["imageTableGateway"] = function (ServiceManager $sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Image());
            return new TableGateway('image', $dbAdapter, null, $resultSetPrototype);
        };
        
        $factories['clientAuctionTable'] = function(ServiceManager $sm) {
            $tableGateway = $sm->get("clientAuctionTableGateway");
            $table = new ClientAuctionTable($tableGateway);
            return $table;
        };
        $factories["clientAuctionTableGateway"] = function (ServiceManager $sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new ClientAuction());
            return new TableGateway('client_auction', $dbAdapter, null, $resultSetPrototype);
        };
        
        $factories['newsletterTable'] = function(ServiceManager $sm) {
            $tableGateway = $sm->get("newsletterTableGateway");
            $table = new NewsletterTable($tableGateway);
            return $table;
        };
        $factories["newsletterTableGateway"] = function (ServiceManager $sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Newsletter());
            return new TableGateway('newsletter', $dbAdapter, null, $resultSetPrototype);
        };
        
        $factories['translateTable'] = function(ServiceManager $sm) {
            $tableGateway = $sm->get("translateTableGateway");
            $table = new TranslateTable($tableGateway);
            return $table;
        };
        $factories["translateTableGateway"] = function (ServiceManager $sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Translate());
            return new TableGateway('translate', $dbAdapter, null, $resultSetPrototype);
        };


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
            if(defined("IS_MAINTENANCE") && IS_MAINTENANCE === true) {
                $router = $sm->get('router');
                $request = $sm->get('request');
                $routeMatch = $router->match($request);
                if($routeMatch->getMatchedRouteName() != 'maintenance') {
                    $e->getTarget()->plugin('redirect')->toRoute('maintenance');
                }
            }
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

    public function getConsoleUsage(Console $console)
    {
    return array(
            // Describe available commands
            'check-enchere'    => 'Update enchere status by end date',
 
            // Describe expected parameters
            array('--verbose|-v','(optional) turn on verbose mode'),
 
    );
    }
}
