<?php

namespace Thexpand\Zf2HttpCache\View\Helper;

use Zend\Http\PhpEnvironment\Request;
use Zend\Http\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\Parameters;
use Zend\Uri\Uri;
use Zend\Uri\UriFactory;
use Zend\View\Helper\AbstractHelper;

class Esi extends AbstractHelper
{
    /**
     * @var array
     */
    private $applicationConfig;

    /**
     * @var Response
     */
    private $response;

    /**
     * Tells whether the client understands surrogate capability
     *
     * @var bool
     */
    private $surrogateCapability;

    /**
     * @var Application
     */
    private $application;

    /**
     * @param array    $applicationConfig
     * @param Response $response
     * @param bool     $surrogateCapability
     */
    public function __construct(
        array $applicationConfig,
        Response $response,
        $surrogateCapability = false
    ) {
        $this->applicationConfig   = $applicationConfig;
        $this->response            = $response;
        $this->surrogateCapability = $surrogateCapability;
    }

    /**
     * When application is not available, one will be initialized to respond to
     * the esi request.
     *
     * @return Application
     */
    public function getApplication(Uri $uri)
    {
        if (! $this->application instanceof Application) {
            $this->application = Application::init($this->applicationConfig);

            // Remove the finish listeners so response header and content is not automatically echo'd
            $this->application->getEventManager()->clearListeners(MvcEvent::EVENT_FINISH);
        }

        /** @var Request $request */
        $request = $this->application->getRequest();

        // The request needs to be augmented with the URI passed in
        $request->setUri($uri);
        $request->setRequestUri($uri->getPath() . '?' . $uri->getQuery());
        $request->setQuery(new Parameters($uri->getQueryAsArray()));

        return $this->application;
    }

    /**
     * By default provides the fluent interface,
     * but can also be invoked with a variable,
     * in which case, it will proxy to escapeHtml
     *
     * @return string|self
     */
    public function __invoke($url = null)
    {
        if (null !== $url) {
            return $this->doEsi($url);
        }

        return $this;
    }

    /**
     * If the client understands surrogate capability, return esi tag.
     * Otherwise, get a new application instance and run it, returning the content.
     *
     * @param string $url
     *
     * @return string
     */
    public function doEsi($url)
    {
        if ($this->surrogateCapability) {
            if ($this->response instanceof Response) {
                $headers = $this->response->getHeaders();
                if (! $headers->has('Surrogate-Control')) {
                    $headers->addHeaderLine('Surrogate-Control', 'ESI/1.0');
                }
            }

            return sprintf('<esi:include src="%s" onerror="continue" />', $url) . PHP_EOL;
        }

        // Fallback to non-surrogate capability
        $uri         = UriFactory::factory($url, 'http');
        $application = $this->getApplication($uri);
        $application->run();

        return $application->getResponse()->getContent();
    }
}
