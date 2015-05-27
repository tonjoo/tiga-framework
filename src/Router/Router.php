<?php
namespace Tiga\Framework\Router;
use Tiga\Framework\Facade\ViewFacade as View;
use Tiga\Framework\Facade\RequestFacade as Request;
use Tiga\Framework\Facade\ApplicationFacade as App;
use Tiga\Framework\Exception\RoutingException as RoutingException;
use FastRoute\Dispatcher as Dispatcher;
use Tiga\Framework\Router\RouteCollector as RouteCollector;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Router 
{

	protected $dispatcher;

	protected $routeInfo;

	protected $routes;

	protected $currentURL;

	function init() 
	{
		$this->routes = App::get('routes')->getRouteCollections();
		
		$this->dispatcher = $this->createDispatcher();

		$this->currentURL = Request::getPathInfo();

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

                if(APP::get('whoops')!==null)
                    APP::get('whoops')->register();
		        
		        $routeHandler = $routeInfo[1];
		        $vars = $routeInfo[2];

		        if(!$routeHandler->isDeferred())
		        {
		        	$handler = $routeHandler->getHandler();
		        	$this->initRouteHandler($handler,$vars);
		        	return;
		        }

		        // Share routeHandler and vars to App
		        App::share('routeHandler',$routeHandler);	
		        App::share('routeHandlerVars',$vars);

		        // Defered route
		        add_action($routeHandler->getRunLevel(),function(){

		        	$handler = App::get('routeHandler')->getHandler();
		        	$vars = App::get('routeHandlerVars');

		        	$this->initRouteHandler($handler,$vars);
		        	
		        },$routeHandler->getPriority());

		        break;
		}

    }

    protected function initRouteHandler($handler,$vars)
    {
    	// Start buffering
        ob_start();
        
        // Handle request
        $response = $this->handle($handler,$vars);

        if(($response instanceof SymfonyResponse)||(is_subclass_of($response,'Symfony\Component\HttpFoundation\Response'))){
        	$response->sendHeaders();

        	View::setResponse($response);
        }

        //Transfer buffer to view
        $content = ob_get_contents();
        
        ob_end_clean();
      

        View::setBuffer($content);
    }

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

