<?php
namespace Frontoffice\Controller\Factory;

class NewsletterControllerFactory implements \Zend\ServiceManager\FactoryInterface {
    public function __invoke(\Interop\Container\ContainerInterface $container, $name, array
$options = null) {
        $parentLocator = $container->getServiceLocator();
        return new \Frontoffice\Controller\NewsletterController( $parentLocator);
    }

    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $container)
    {
        return $this($container, \Frontoffice\Controller\NewsletterController::class);
    }
}