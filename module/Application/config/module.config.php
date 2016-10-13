<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'home_back' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/back',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'clients' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/back/clients',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Client',
                        'action'     => 'list',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'edit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/edit[/:client_id]',
                            'constraints' => array(
                                'client_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'edit',
                            ),
                        ),
                    ),
                    'save' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/save[/:client_id]',
                            'constraints' => array(
                                'client_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'save',
                            ),
                        ),
                    ),
                    'delete' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/delete/:client_id',
                            'constraints' => array(
                                'client_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'delete',
                            ),
                        ),
                    ),
                ),
            ),
            'translate' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/back/translate',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Translate',
                        'action'     => 'list',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'edit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/edit[/:translate_id]',
                            'constraints' => array(
                                'translate_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'edit',
                            ),
                        ),
                    ),
                    'save' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/save[/:translate_id]',
                            'constraints' => array(
                                'translate_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'save',
                            ),
                        ),
                    ),
                    'delete' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/delete/:translate_id',
                            'constraints' => array(
                                'translate_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'delete',
                            ),
                        ),
                    ),
                ),
            ),
            'sellers' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/back/sellers',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Vendeur',
                        'action'     => 'list',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'edit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/edit[/:vendeur_id]',
                            'constraints' => array(
                                'vendeur_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'edit',
                            ),
                        ),
                    ),
                    'save' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/save[/:vendeur_id]',
                            'constraints' => array(
                                'vendeur_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'save',
                            ),
                        ),
                    ),
                    'delete' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/delete/:vendeur_id',
                            'constraints' => array(
                                'vendeur_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'delete',
                            ),
                        ),
                    ),
                ),
            ),
            'chevaux' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/back/chevaux',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Chevaux',
                        'action'     => 'list',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'edit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/edit[/:cheval_id]',
                            'constraints' => array(
                                'cheval_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'edit',
                            ),
                        ),
                    ),
                    'save' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/save[/:cheval_id]',
                            'constraints' => array(
                                'cheval_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'save',
                            ),
                        ),
                    ),
                    'delete' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/delete/:cheval_id',
                            'constraints' => array(
                                'cheval_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'delete',
                            ),
                        ),
                    ),
                ),
            ),
            'encheres' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/back/encheres',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Encheres',
                        'action'     => 'list',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'edit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/edit[/:enchere_id]',
                            'constraints' => array(
                                'enchere_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'edit',
                            ),
                        ),
                    ),
                    'save' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/save[/:enchere_id]',
                            'constraints' => array(
                                'enchere_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'save',
                            ),
                        ),
                    ),
                    'delete' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/delete/:enchere_id',
                            'constraints' => array(
                                'enchere_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'delete',
                            ),
                        ),
                    ),
                ),
            ),
            'lots' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/back/encheres/:enchere_id/lots',
                    'constraints' => array(
                        'enchere_id' => '[0-9]+',
                    ),
                    'defaults' => array(
                    'controller' => 'Application\Controller\Lot',
                    'action'     => 'list',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'edit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/edit[/:lot_id]',
                            'constraints' => array(
                                'lot_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'edit',
                            ),
                        ),
                    ),
                    'save' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/save[/:lot_id]',
                            'constraints' => array(
                                'lot_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'save',
                            ),
                        ),
                    ),
                    'delete' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/delete/:lot_id',
                            'constraints' => array(
                                'lot_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'delete',
                            ),
                        ),
                    ),
                    "encheres" => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/:lot_id/encheres',
                            'constraints' => array(
                                'lot_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'enchere',
                            ),
                        ),
                    ),
                    "media_list" => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/:lot_id/list_file',
                            'constraints' => array(
                                'lot_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'mediaList',
                            ),
                        ),
                    ),
                    "media_upload" => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/:lot_id/upload',
                            'constraints' => array(
                                'lot_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'mediaUpload',
                            ),
                        ),
                    ),

                    'media_delete' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/:lot_id/delete',
                            'constraints' => array(
                                'lot_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'mediaDelete',
                            ),
                        ),
                    ),

                    'media_update' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/:lot_id/update',
                            'constraints' => array(
                                'lot_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'mediaUpdate',
                            ),
                        ),
                    ),
                ),
            ),
            'newsletter' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/back/newsletter',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Newsletter',
                        'action'     => 'list',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'edit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/edit',
                            'defaults' => array(
                                'action'     => 'edit',
                            ),
                        ),
                    ),
                    'save' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/save',
                            'defaults' => array(
                                'action'     => 'save',
                            ),
                        ),
                    ),
                    'delete' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/delete/:newsletter_id',
                            'constraints' => array(
                                'newsletter_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action'     => 'delete',
                            ),
                        ),
                    ),
                ),
            ),
            

            'login' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/back/login',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Login',
                        'action'     => 'login',
                    ),
                ),
            ),

            'save_login' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/back/savelogin',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Login',
                        'action'     => 'savelogin',
                    ),
                ),
            ),

            'logout' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/back/logout',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Login',
                        'action'     => 'logout',
                    ),
                ),
            ),


            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/back/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
        'factories' => array(
            'logger' => function($sm){
                $logger = Application\Service\LoggerApp::getInstance();
                return $logger;
            },
            'user_service' => function($sm) { 
				return new Application\Service\UserService($sm); 
			}
            /*'Aclplugin' => function($sm) {
                return new Application\Plugin\Aclplugin($sm);
            },*/
        ),
    ),
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Login' => 'Application\Controller\LoginController',
            'Application\Controller\Chevaux' => 'Application\Controller\ChevauxController',
            'Application\Controller\Vendeur' => 'Application\Controller\VendeurController',
            'Application\Controller\Client' => 'Application\Controller\ClientController',
            'Application\Controller\Encheres' => 'Application\Controller\EncheresController',
            'Application\Controller\Lot' => 'Application\Controller\LotController',
            'Application\Controller\Newsletter' => 'Application\Controller\NewsletterController',
            'Application\Controller\Translate' => 'Application\Controller\TranslateController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout_back'      => __DIR__ . '/../view/layout/layout_back.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'session' => array(
        'remember_me_seconds'  => 14400,
        'use_cookies'		  => true,
        'cookie_httponly'	  => true,
        'cookie_domain'		=> '', 
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(

            ),
        ),
    ),
    'application_config' => array(

    ),
    'form_elements' => array(
    'invokables' => array(
        'client_edit' => 'Application\Form\ClientForm',
        'vendeur_edit' => 'Application\Form\VendeurForm',
        'chevaux_edit' => 'Application\Form\ChevalForm',
        'encheres_edit' => 'Application\Form\EnchereForm',
        'lots_edit' => 'Application\Form\LotForm',
        'newsletter_edit' => 'Application\Form\NewsletterForm',
        'translate_edit' => 'Application\Form\TranslateForm',
    )
)
    /*'controller_plugins' => array(
        'invokables' => array(
           'Aclplugin' => 'Application\Core\Plugin\Aclplugin',
         )
     ),*/
);
