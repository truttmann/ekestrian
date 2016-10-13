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
            /*
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'RepriseBatch\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            */

            // generation de la table a plat
            'buildFlatTable' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/build_flat',
                    'defaults' => array(
                        'controller' => 'RepriseBatch\Controller\FlatCamping',
                        'action'     => 'load',
                    ),
                ),
            ),
            'batch_remplissage_flat_index_search' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/batch_index_search',
                    'defaults' => array(
                        'controller' => 'RepriseBatch\Controller\Batch',
                        'action'     => 'remplissageIndexSearch',
                    ),
                ),
            ),
            'batch_contract' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/batch_contract',
                    'defaults' => array(
                        'controller' => 'RepriseBatch\Controller\Batch',
                        'action'     => 'contract',
                    ),
                ),
            ),

            // pour l'import xml quotidien
            'importXML' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/import_xml',
                    'defaults' => array(
                        'controller' => 'RepriseBatch\Controller\Batch',
                        'action'     => 'debutImport',
                    ),
                ),
            ),


            // pour l'import csv des logo partenaire
            'importCSVLogoPartenaire' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/import_logo_partenaire',
                    'defaults' => array(
                        'controller' => 'RepriseBatch\Controller\Batch',
                        'action'     => 'logoPartenaire',
                    ),
                ),
            ),

            // pour l'import csv des logo partenaire
            'importCSVLienReservation' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/import_lien_reservation',
                    'defaults' => array(
                        'controller' => 'RepriseBatch\Controller\Batch',
                        'action'     => 'lienReservation',
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
                    'route'    => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'RepriseBatch\Controller',
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
                $logger = RepriseBatch\Service\LoggerApp::getInstance();
                /*$writer = new Zend\Log\Writer\Stream('../../.../log/'.date('Y-m-d').'-error.log');             
                $logger->addWriter($writer);  
                 
                */
                return $logger;
            },
        ),
    ),
    /*'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),*/
    'controllers' => array(
        'invokables' => array(
            'RepriseBatch\Controller\Index' => 'RepriseBatch\Controller\IndexController',
            'RepriseBatch\Controller\Batch' => 'RepriseBatch\Controller\BatchController',
            'RepriseBatch\Controller\FlatCamping' => 'RepriseBatch\Controller\FlatCampingController',
            'RepriseBatch\Controller\City' => 'RepriseBatch\Controller\CityController',
            'RepriseBatch\Controller\Camping' => 'RepriseBatch\Controller\CampingController',
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
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
                'importXML' => array(
                    'options' => array(
                        'route'    => 'import_xml [--forced|-f]:forced',
                        'defaults' => array(
                            'controller' => 'RepriseBatch\Controller\Batch',
                            'action'     => 'debutImport',
                        ),
                    ),
                ),
                'batch_remplissage_index_search' => array(
                    'options' => array(
                        'route'    => 'batch_index_search',
                        'defaults' => array(
                            'controller' => 'RepriseBatch\Controller\Batch',
                            'action'     => 'remplissageIndexSearch',
                        ),
                    ),
                ),
                'batch_remplissage_flat_camping' => array(
                    'options' => array(
                        'route'    => 'batch_flat_camping [--forced|-f]:forced',
                        'defaults' => array(
                            'controller' => 'RepriseBatch\Controller\FlatCamping',
                            'action'     => 'load',
                        ),
                    ),
                ),
                'batch_contract' => array(
                    'options' => array(
                        'route'    => 'batch_contract',
                        'defaults' => array(
                            'controller' => 'RepriseBatch\Controller\Batch',
                            'action'     => 'contract',
                        ),
                    ),
                ),
                // pour l'import csv des logo partenaire
                'importCSVLogoPartenaire' => array(
                    'options' => array(
                        'route'    => 'import_logo_partenaire',
                        'defaults' => array(
                            'controller' => 'RepriseBatch\Controller\Batch',
                            'action'     => 'logoPartenaire',
                        ),
                    ),
                ),
                // pour l'import csv des logo partenaire
                'importCSVLienReservation' => array(
                    'options' => array(
                        'route'    => 'import_lien_reservation',
                        'defaults' => array(
                            'controller' => 'RepriseBatch\Controller\Batch',
                            'action'     => 'lienReservation',
                        ),
                    ),
                ),
                // pour l'import csv des sites touristiques
                'importCSVPaysAcceuil' => array(
                    'options' => array(
                        'route'    => 'import_pays_acceuil',
                        'defaults' => array(
                            'controller' => 'RepriseBatch\Controller\Batch',
                            'action'     => 'paysAcceuil',
                        ),
                    ),
                ),
                'generateCityDescription' => array(
                    'options' => array(
                        'route'    => 'generate_city_description [--id=] [--lang=] [--display|-d]:display [--forced|-f]:forced',
                        'defaults' => array(
                            'controller' => 'RepriseBatch\Controller\City',
                            'action'     => 'load',
                        ),
                    ),
                ),
                'generateCampingDescription' => array(
                    'options' => array(
                        'route'    => 'generate_camping_description [--forced|-f]:forced',
                        'defaults' => array(
                            'controller' => 'RepriseBatch\Controller\Camping',
                            'action'     => 'load',
                        ),
                    ),
                ),
            ),
        ),
    ),
    'application_config' => array(
        'import_xml' => array(
            'url_fichier' => "interfaces/import-campings/depots",
            'url_fichier_config' => "module/RepriseBatch/config/config_import_xml.php",
            'url_archive' => "interfaces/import-campings/archives",
            'url_erreur' => "interfaces/import-campings/erreur",
            'poids_maxi' => 65536,      // 64ko
            'heure_limit' => '01:00:00',
            'heure_limit_fin' => '03:00:00',
        ),
        'contract' => array(
            'id_product_url' => 1,
        ),
        'logo_partenaire' => array(
            'url_fichier' => "interfaces/import-partenaires/depots/LiensPartenaires.csv",
            'url_archive' => "interfaces/import-partenaires/archives",
            'url_erreur' => "interfaces/import-partenaires/erreur",
            'carac_separateur' => ";",
            'line_depart' => 1,
        ),
        'lien_reservation' => array(
            'url_fichier' => "interfaces/import-partenaires/depots/LiensReservation.csv",
            'url_archive' => "interfaces/import-partenaires/archives",
            'url_erreur' => "interfaces/import-partenaires/erreur",
            'carac_separateur' => ";",
            'line_depart' => 1,
        ),
        'site_touristique' => array(
            'url_fichier' => "interfaces/import-partenaires/depots/SiteTouristique.csv",
            'url_archive' => "interfaces/import-partenaires/archives",
            'url_erreur' => "interfaces/import-partenaires/erreur",
            'carac_separateur' => ";",
            'line_depart' => 1,
        ),
        'batch' => array(
            'repository_for_lockfile' => __DIR__."/../../../log/batch/",
            'ds' => "/",
        )
    )
);
