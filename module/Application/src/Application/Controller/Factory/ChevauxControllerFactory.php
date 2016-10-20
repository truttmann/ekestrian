<?php
namespace Application\Controller\Factory;

class ChevauxControllerFactory implements \Zend\ServiceManager\FactoryInterface {
    
    public function __invoke(\Interop\Container\ContainerInterface $container, $name, array
$options = null) {
        $parentLocator = $container->getServiceLocator();
        return new $name( $parentLocator);
    }

    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $container)
    {
        return $this($container, \Application\Controller\ChevauxController::class);
    }
}