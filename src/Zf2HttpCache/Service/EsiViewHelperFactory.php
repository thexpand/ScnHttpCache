<?php

namespace Thexpand\Zf2HttpCache\Service;

use Thexpand\Zf2HttpCache\View\Helper\Esi;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\HelperPluginManager;

class EsiViewHelperFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface|HelperPluginManager $viewHelperPluginManager
     *
     * @return Esi
     */
    public function createService(ServiceLocatorInterface $viewHelperPluginManager)
    {
        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $viewHelperPluginManager->getServiceLocator();

        /** @var array $applicationConfig */
        $applicationConfig = $serviceLocator->get('ApplicationConfig');

        /** @var Response $response */
        $response = $serviceLocator->get('Response');

        /** @var Request $request */
        $request = $serviceLocator->get('Request');

        $headers             = $request->getHeaders();
        $surrogateCapability = $headers->has('surrogate-capability')
            && strpos($headers->get('surrogate-capability')->getFieldValue(), 'ESI/1.0') !== false;

        return new Esi(
            $applicationConfig,
            $response,
            $surrogateCapability
        );
    }
}
