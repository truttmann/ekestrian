<?php
namespace Application\Service\Factory;

class MongopayServiceFactory implements \Zend\ServiceManager\FactoryInterface {
    
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $container)
    {
        return new \Application\Service\MongopayService($container);
    }
}