<?php
namespace Application\Controller\Factory;

class LotControllerFactory implements \Zend\ServiceManager\FactoryInterface {
    public function __invoke(\Interop\Container\ContainerInterface $container, $name, array
$options = null) {
        $parentLocator = $container->getServiceLocator();
        return new \Application\Controller\LotController( $parentLocator);
    }

    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $container)
    {
        return $this($container, \Application\Controller\LotController::class);
    }
}