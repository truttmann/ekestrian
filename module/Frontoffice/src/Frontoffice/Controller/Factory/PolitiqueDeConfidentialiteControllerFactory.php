<?php
namespace Frontoffice\Controller\Factory;

class PolitiqueDeConfidentialiteControllerFactory implements \Zend\ServiceManager\FactoryInterface {
    public function __invoke(\Interop\Container\ContainerInterface $container, $name, array
$options = null) {
        $parentLocator = $container->getServiceLocator();
        return new \Frontoffice\Controller\PolitiqueDeConfidentialiteController( $parentLocator);
    }

    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $container)
    {
        return $this($container, \Application\Controller\PolitiqueDeConfidentialiteController::class);
    }
}