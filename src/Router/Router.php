<?php
namespace Tiga\Framework\Router;

use Tiga\Framework\App;
use Tiga\Framework\View\View;
use Tiga\Framework\Exception\RoutingException as RoutingException;
use FastRoute\Dispatcher as Dispatcher;
use Tiga\Framework\Router\RouteCollector as RouteCollector;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tiga\Framework\Request;
use Tiga\Framework\Router\Routes;

class Router 
{

	protected $dispatcher;

	protected $routeInfo;

    protected $routes;
    protected $request;
	protected $view;

	protected $currentURL;

    protected $protectedRoute = array('POST','DELETE', 'PATCH', 'PUT');

    function __construct(Routes $routes,Request $request,App $app,View $view)
    {
        $this->routes = $routes;
        $this->request = $request;
        $this->app = $app;
        $this->view = $view;

    }

	function init() 
	{
		$this->routes = $this->routes->getRouteCollections();
		
		$this->dispatcher = $this->createDispatcher();

		$this->currentURL = $this->request->getPathInfo();

		$this->dispatch(); 

	}

	protected function createDispatcher()
    {
    	$options = array();

        $options += [
       		'routeParser' => 'FastRoute\\RouteParser\\Std',
       	 	'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
      	  	'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
    	];

	    $routeCollector = new RouteCollector(
	        new $options['routeParser'], new $options['dataGenerator']
	    );

        $routeCollector->setRequest($this->request);

	    foreach ($this->routes as $route) {
	    	
            $routeCollector->addRoute($route->getMethod(), $route->getConvertedRoute(), $route->getRouteHandler());
        
        }

	    return new $options['dispatcher']($routeCollector->getData());
    }

    protected function dispatch() 
    {
    	$routeInfo = $this->dispatcher->dispatch($_SERVER['REQUEST_METHOD'],$this->currentURL);
  
		switch ($routeInfo[0]) 
		{
		    case Dispatcher::NOT_FOUND:
		       	// Continue to WordPress

		        break;
		    case Dispatcher::METHOD_NOT_ALLOWED:
		        $allowedMethods = $routeInfo[1];
		        // Sample : ... 405 Method Not Allowed
		        break;
		    case Dispatcher::FOUND:

                if($this->app->get('whoops')!==null)
                    $this->app->get('whoops')->register();

                // Hook WordPress Template
                $this->view->hook();
		        
                // Get route parameter
		        $routeHandler = $routeInfo[1];
		        $vars = $routeInfo[2];

                // Protected route, check the token
                if(in_array($this->request->getMethod(),$this->protectedRoute)||$this->request->isXmlHttpRequest())
                    $this->request->checkToken();

                // Check if route is deffered
		        if(!$routeHandler->isDeferred())
		        {		        
		        	$this->initRouteHandler($routeHandler,$vars);
		        	return;
		        }

		        // Share routeHandler and vars to App
		        $this->app->share('routeHandler',$routeHandler);	
		        $this->app->share('routeHandlerVars',$vars);

		        // Defered route callback
		        add_action($routeHandler->getRunLevel(),function(){

		        	$routeHandler = $this->app->get('routeHandler');
		        	$vars = $this->app->get('routeHandlerVars');

		        	$this->initRouteHandler($routeHandler,$vars);
		        	
		        },$routeHandler->getPriority());

		        break;
		}

    }

    protected function initRouteHandler($routeHandler,$vars)
    {
    	// Start buffering
        ob_start();
        
        // Handle request
        $response = $this->handle($routeHandler->getHandler(),$vars);

        if(($response instanceof SymfonyResponse)||(is_subclass_of($response,'Symfony\Component\HttpFoundation\Response'))){
        	
            $response->sendHeaders();

        	$this->view->setResponse($response);
        }

        //Transfer buffer to view
        $content = ob_get_contents();
        
        ob_end_clean();
      
        // Set Buffer to View
        $this->view->setBuffer($content);

        // JSON Response
        if(is_subclass_of($response,'Symfony\Component\HttpFoundation\Response')&&$response->isJson())
        {
            $this->view->render();
            die();
        }
        
        //Fast Exit
        if($routeHandler->isFastExit())
        {
            $this->view->render();
            die();
        }
    }

    /*
     * Handle Route Handler Callback
     */
    protected function handle($handler,$vars) 
    {
    	if (is_callable($handler)) 
    	{
            // The action is an anonymous function, let's execute it.
	        return call_user_func_array($handler, $vars);

           
        }
        else if (is_string($handler) ) 
        {

            //set default method to index
            if(!strpos($handler,'@'))
                $handler = $handler."@index";
    
            list($controller, $method) = explode('@', $handler);

            $class = basename($controller);
       
            // The controller class was still not found. Let the next routes handle the
            // request.
            if (!class_exists($class))
               throw new RoutingException("{$class} not found");


            // @TODO, pass constructor parameters functions
            $instance = new $class();

            //check method exist
            if(!method_exists($instance, $method))
                throw new RoutingException("{$class} does'nt have method {$method}");

            return call_user_func_array(array($instance, $method), $vars);

        }
        
    }

}

