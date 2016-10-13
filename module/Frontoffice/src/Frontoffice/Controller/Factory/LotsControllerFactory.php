<?php
namespace Frontoffice\Controller\Factory;

class LotsControllerFactory implements \Zend\ServiceManager\FactoryInterface {
    public function __invoke(\Interop\Container\ContainerInterface $container, $name, array
$options = null) {
        $parentLocator = $container->getServiceLocator();
        return new \Frontoffice\Controller\LotsController( $parentLocator);
    }

    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $container)
    {
        return $this($container, \Application\Controller\LotsController::class);
    }
}