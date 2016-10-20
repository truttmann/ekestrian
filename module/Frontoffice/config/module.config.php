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
                    'validation_creation'=>  array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route'    => '/validation_creation',
                            'defaults' => array(
                                'controller' => 'Membre',
                                'action'     => 'validationCreation',
                            ),
                        ),
                    ),
                    'validation_membre'=>  array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route'    => '/validation_membre/:token',
                            'defaults' => array(
                                'controller' => 'Membre',
                                'action'     => 'validationAccount',
                            ),
                        ),
                    ),
                    'forgotpassword'=>  array(
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => array(
                            'route'    => '/forgotpassword',
                            'defaults' => array(
                                'controller' => 'Membre',
                                'action'     => 'forgotpassword',
                            ),
                        ),
                    ),
                    'membre' =>  array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route'    => '/membre',
                            'defaults' => array(
                                'controller' => 'Membre',
                                'action'     => 'authentification',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'login' =>  array(
                                'type' => 'Zend\Mvc\Router\Http\Literal',
                                'options' => array(
                                    'route'    => '/login',
                                    'defaults' => array(
                                        'action'     => 'login',
                                    ),
                                ),
                            ),
                            'enchere' =>  array(
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => array(
                                    'route'    => '/:membre_id/enchere',
                                    'constraints' => array(
                                        'membre_id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'action'     => 'listenchere',
                                    ),
                                ),
                            ),
                            'logout' =>  array(
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => array(
                                    'route'    => '/:membre_id/logout',
                                    'constraints' => array(
                                        'membre_id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'action'     => 'logout',
                                    ),
                                ),
                            ),
                            'edit' =>  array(
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => array(
                                    'route'    => '/edit[/:membre_id]',
                                    'constraints' => array(
                                        'membre_id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'action'     => 'edit',
                                    ),
                                ),
                            ),
                            'save' =>  array(
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => array(
                                    'route'    => '/save[/:membre_id]',
                                    'constraints' => array(
                                        'membre_id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'action'     => 'save',
                                    ),
                                ),
                            ),
                            'edit_cart' =>  array(
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => array(
                                    'route'    => '/edit/:membre_id/carte',
                                    'constraints' => array(
                                        'membre_id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'action'     => 'carte',
                                    ),
                                ),
                            ),
                            'register_cart' =>  array(
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => array(
                                    'route'    => '/edit/:membre_id/register_carte',
                                    'constraints' => array(
                                        'membre_id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'action'     => 'carteRegister',
                                    ),
                                ),
                            ),
                            'retour_cart' =>  array(
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => array(
                                    'route'    => '/edit/:membre_id/carte_retour',
                                    'constraints' => array(
                                        'membre_id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'action'     => 'carteRetour',
                                    ),
                                ),
                            ),
                            'retour_preauth' =>  array(
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => array(
                                    'route'    => '/:membre_id/carte_retour_preauth',
                                    'constraints' => array(
                                        'membre_id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'action'     => 'carteRetourPreAuth',
                                    ),
                                ),
                            ),
                        ),
                    ),                    
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
                    'cgu' =>  array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route'    => '/cgu',
                            'defaults' => array(
                                'controller' => 'CGU',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    'politique_de_confidentialite' =>  array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route'    => '/MentionsLegales',
                            'defaults' => array(
                                'controller' => 'PolitiqueDeConfidentialite',
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
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'information' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => array(
                                            'route'    => '/information',
                                            'defaults' => array(
                                                'action'     => 'information',
                                            ),
                                        ),
                                    ),
                                    'validation' => array(
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => array(
                                            'route'    => '/validation',
                                            'defaults' => array(
                                                'action'     => 'validation',
                                            ),
                                        ),
                                    ),
                                    'reload_cost' =>  array(
                                        'type' => 'Zend\Mvc\Router\Http\Segment',
                                        'options' => array(
                                            'route'    => '/reload_cost',
                                            'constraints' => array(
                                                'membre_id' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'action'     => 'reloadCost',
                                            ),
                                        ),
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
        ),
    ),
    'controllers' => array(
        'factories' => array(
            'Index' => 'Frontoffice\Controller\Factory\IndexControllerFactory',
            'Lots' => 'Frontoffice\Controller\Factory\LotsControllerFactory',
            'Lot' => 'Frontoffice\Controller\Factory\LotControllerFactory',
            'Partenaire' => 'Frontoffice\Controller\Factory\PartenaireControllerFactory',
            'CGU' => 'Frontoffice\Controller\Factory\CGUControllerFactory',
            'PolitiqueDeConfidentialite' => 'Frontoffice\Controller\Factory\PolitiqueDeConfidentialiteControllerFactory',
            'Membre' => 'Frontoffice\Controller\Factory\MembreControllerFactory',
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
    ),
    'form_elements' => array(
        'invokables' => array(
            'membre_edit' => 'Frontoffice\Form\MembreForm',
        )
    )
    /*'controller_plugins' => array(
        'invokables' => array(
           'Aclplugin' => 'Application\Core\Plugin\Aclplugin',
         )
     ),*/
);
