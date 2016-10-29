<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=ekestriajsencher;host=ekestriajsencher.mysql.db',
        'username'       => 'ekestriajsencher',
        'password'       => 'Enchere1',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
                    => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
    'slm_locale' => array(
        'default' => 'fr-FR',
        'supported' => array('en-GB'),
        'strategies' => array(
            array(
                "name" => 'uripath',
                "options" => array (
                    "aliases" => array("fr" => "fr-FR", "en" => "en-GB")
                ),
                'priority' => 1
            ),
            array(
                'name' =>'acceptlanguage',
                'priority' => 2
            )
        ),
    ),
    /*'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'myapp',
                'remember_me_seconds' => 4 * 60 * 60,
                'cookieLifetime' => 4 * 60 * 60,
                'useCookies' => true
            ),
        ),
    ),*/
);
