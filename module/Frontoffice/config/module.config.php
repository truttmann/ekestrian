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
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/[:lang]',
                    'constraints' => array(
                        'lang' => '[a-z]{2}',
                    ),
                    'defaults' => array(
                        'controller' => 'Index',
                        'action'     => 'index',
                        'lang'       => 'fr'
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'partenaire' =>  array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route'    => '/partenaire',
                            'defaults' => array(
                                'controller' => 'Partenaire',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    'lots' => array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route'    => '/enchere/:enchere_id',
                            'constraints' => array(
                                'enchere_id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'Lots',
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'lot' => array(
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => array(
                                    'route'    => '/lot/:lot_id',
                                    'constraints' => array(
                                        'lot_id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'Lot',
                                        'action'     => 'index',
                                    ),
                                ),
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
        ),
    ),
    'controllers' => array(
        'factories' => array(
            'Index' => 'Frontoffice\Controller\Factory\IndexControllerFactory',
            'Lots' => 'Frontoffice\Controller\Factory\LotsControllerFactory',
            'Lot' => 'Frontoffice\Controller\Factory\LotControllerFactory',
            'Partenaire' => 'Frontoffice\Controller\Factory\PartenaireControllerFactory',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
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
            
    'view_helpers' => array(
        'invokables' => array(
            'translate' => 'Frontoffice\Helper\Factory\TranslateFactory'
        )
    )
    /*'controller_plugins' => array(
        'invokables' => array(
           'Aclplugin' => 'Application\Core\Plugin\Aclplugin',
         )
     ),*/
);
