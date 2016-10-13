<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RepriseBatch;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use RepriseBatch\Model\Country;
use RepriseBatch\Model\CountryTable;
use RepriseBatch\Model\Region;
use RepriseBatch\Model\RegionTable;
use RepriseBatch\Model\Department;
use RepriseBatch\Model\DepartmentTable;
use RepriseBatch\Model\City;
use RepriseBatch\Model\CityTable;
use RepriseBatch\Model\Camping;
use RepriseBatch\Model\CampingTable;
use RepriseBatch\Model\Caracteristic;
use RepriseBatch\Model\CaracteristicTable;
use RepriseBatch\Model\CampingCaracteristic;
use RepriseBatch\Model\CampingCaracteristicTable;
use RepriseBatch\Model\IndexSearch;
use RepriseBatch\Model\IndexSearchTable;
use RepriseBatch\Model\Language;
use RepriseBatch\Model\LanguageTable;
use RepriseBatch\Model\Thematic;
use RepriseBatch\Model\ThematicTable;
use RepriseBatch\Model\User;
use RepriseBatch\Model\UserTable;
use RepriseBatch\Model\Contract;
use RepriseBatch\Model\ContractTable;
use RepriseBatch\Model\Partner;
use RepriseBatch\Model\PartnerTable;
use RepriseBatch\Model\GoodPlan;
use RepriseBatch\Model\GoodPlanTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module implements ConsoleUsageProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $this->loadConstant();
    }

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

    public function getConsoleUsage(Console $console)
    {
        return array(
            'batch_flat_camping [--forced|-f]'      => 'Update or insert datas in _flat_camping table',
            'generate_city_description [--forced|-f] [--id=]'     => 'Generate cities descriptions',
            'generate_camping_description [--forced|-f] [--id=]'     => 'Generate camping descriptions'
        );
    }

    // Gestion de l acces a la base de donnees pour toutes les entities
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'RepriseBatch\Model\CountryTable' =>  function($sm) {
                    $tableGateway = $sm->get('CountryTableGateway');
                    $table = new CountryTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\RegionTable' =>  function($sm) {
                    $tableGateway = $sm->get('RegionTableGateway');
                    $table = new RegionTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\DepartmentTable' =>  function($sm) {
                    $tableGateway = $sm->get('DepartmentTableGateway');
                    $table = new DepartmentTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\CityTable' =>  function($sm) {
                    $tableGateway = $sm->get('CityTableGateway');
                    $table = new CityTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\CampingTable' =>  function($sm) {
                    $tableGateway = $sm->get('CampingTableGateway');
                    $table = new CampingTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\CaracteristicTable' =>  function($sm) {
                    $tableGateway = $sm->get('CaracteristicTableGateway');
                    $table = new CaracteristicTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\CampingCaracteristicTable' =>  function($sm) {
                    $tableGateway = $sm->get('CampingCaracteristicTableGateway');
                    $table = new CampingCaracteristicTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\IndexSearchTable' =>  function($sm) {
                    $tableGateway = $sm->get('IndexSearchTableGateway');
                    $table = new IndexSearchTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\LanguageTable' =>  function($sm) {
                    $tableGateway = $sm->get('LanguageTableGateway');
                    $table = new LanguageTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\ThematicTable' =>  function($sm) {
                    $tableGateway = $sm->get('ThematicTableGateway');
                    $table = new ThematicTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\UserTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\ContractTable' =>  function($sm) {
                    $tableGateway = $sm->get('ContractTableGateway');
                    $table = new ContractTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\PartnerTable' =>  function($sm) {
                    $tableGateway = $sm->get('PartnerTableGateway');
                    $table = new PartnerTable($tableGateway);
                    return $table;
                },
                'RepriseBatch\Model\GoodPlanTable' =>  function($sm) {
                    $tableGateway = $sm->get('GoodPlanTableGateway');
                    $table = new GoodPlanTable($tableGateway);
                    return $table;
                },
                'CountryTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Country());
                    return new TableGateway('country', $dbAdapter, null, $resultSetPrototype);
                },
                'RegionTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Region());
                    return new TableGateway('region', $dbAdapter, null, $resultSetPrototype);
                },
                'DepartmentTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Department());
                    return new TableGateway('department', $dbAdapter, null, $resultSetPrototype);
                },
                'CityTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new City());
                    return new TableGateway('city', $dbAdapter, null, $resultSetPrototype);
                },
                'CampingTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Camping());
                    return new TableGateway('camping', $dbAdapter, null, $resultSetPrototype);
                },
                'CaracteristicTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Caracteristic());
                    return new TableGateway('caracteristic', $dbAdapter, null, $resultSetPrototype);
                },
                'CampingCaracteristicTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new CampingCaracteristic());
                    return new TableGateway('camping_caracteristic', $dbAdapter, null, $resultSetPrototype);
                },
                'IndexSearchTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new IndexSearch());
                    return new TableGateway('_index_search', $dbAdapter, null, $resultSetPrototype);
                },
                'LanguageTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Language());
                    return new TableGateway('language', $dbAdapter, null, $resultSetPrototype);
                },
                'ThematicTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Thematic());
                    return new TableGateway('thematic', $dbAdapter, null, $resultSetPrototype);
                },
                'UserTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    return new TableGateway('user', $dbAdapter, null, $resultSetPrototype);
                },
                'ContractTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Contract());
                    return new TableGateway('contract', $dbAdapter, null, $resultSetPrototype);
                },
                'PartnerTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Partner());
                    return new TableGateway('partner', $dbAdapter, null, $resultSetPrototype);
                },
                'GoodPlanTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new GoodPlan());
                    return new TableGateway('good_plan', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
