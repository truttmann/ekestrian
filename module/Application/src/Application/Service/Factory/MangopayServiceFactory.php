<?php
namespace Application\Service\Factory;

class MangopayServiceFactory implements \Zend\ServiceManager\FactoryInterface {
    
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $container)
    {
        return new \Application\Service\MangopayService($container);
    }
}