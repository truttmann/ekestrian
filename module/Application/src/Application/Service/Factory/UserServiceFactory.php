<?php
namespace Application\Service\Factory;

class UserServiceFactory implements \Zend\ServiceManager\FactoryInterface {
    
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $container)
    {
        return new \Application\Service\UserService($container);
    }
}