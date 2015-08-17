<?php

namespace Tiga\Framework\Router;

use Tiga\Framework\Request;

class RouteCollector extends \FastRoute\RouteCollector
{
    /**
     * @var string
     */
    protected $request;

    /**
     * Get current request path.
     *
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Add trailing slash route or add route without trailing slash so both work.
     *
     * @param string       $httpMethod
     * @param string       $route
     * @param RouteHandler $handler
     */
    public function addRoute($httpMethod, $route, $handler)
    {
        parent::addRoute($httpMethod, $route, $handler);

        // Route ended with slash , redirect non slash to slash
        if (substr($route, -1) === '/') {
            parent::addRoute($httpMethod, substr($route, 0, -1),
                new RouteHandler(
                    function () {

                        $url = tiga_url($this->request->getPathInfo().'/');

                        $response = \Tiga\Framework\Response\ResponseFactory::redirect($url);
                        $response->sendHeaders();
                        die();

                    })
                );
        }
        // Route ended with no slash , redirect slash to no slash
        else {
            $route .= '/';
            parent::addRoute($httpMethod, $route,
                new RouteHandler(
                    function () {
                        $url = rtrim(tiga_url($this->request->getPathInfo()), '/');

                        $response = \Tiga\Framework\Response\ResponseFactory::redirect($url);
                        $response->sendHeaders();
                        die();

                    })
                );
        }
    }
}
