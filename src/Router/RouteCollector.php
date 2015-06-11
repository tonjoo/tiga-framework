<?php
namespace Tiga\Framework\Router;

class RouteCollector extends \FastRoute\RouteCollector 
{
    public function addRoute($httpMethod, $route, $handler) 
    {
        parent::addRoute($httpMethod, $route, $handler);

        // Route ended with slash , redirect non slash to slash
        if (substr($route, -1) === '/') {
            parent::addRoute($httpMethod, substr($route, 0, -1), 
                new RouteHandler(
                    function() {

                        $url = tiga_url($this->request->getPathInfo()."/");

                        $response =  \Tiga\Framework\Response\ResponseFactory::redirect($url);
                        $response->sendHeaders();
                        die();

                    })
                );
        }
        // Route ended with no slash , redirect slash to no slash
        else
        {
            $route .= "/";
            parent::addRoute($httpMethod, $route,
                new RouteHandler(
                    function(){
                        $url = rtrim(tiga_url($this->request->getPathInfo()), '/');

                        $response =  \Tiga\Framework\Response\ResponseFactory::redirect($url);
                        $response->sendHeaders();
                        die();

                    })
                );
        }
    }
}