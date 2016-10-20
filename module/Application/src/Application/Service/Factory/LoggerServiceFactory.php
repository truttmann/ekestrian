<?php
namespace Application\Service\Factory;

class LoggerServiceFactory implements \Zend\ServiceManager\FactoryInterface {
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $container)
    {
        return Application\Service\LoggerApp::getInstance();
    }
}