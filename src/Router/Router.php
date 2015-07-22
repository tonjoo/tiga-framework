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
use Tiga\Framework\Response\Header;
use Tiga\Framework\Response\ResponseFactory;
use Tiga\Framework\Menu;

class Router 
{

    /**
     * Router dispatcher
     * @var \FastRoute\Dispatcher;
     */ 
	protected $dispatcher;

    /**
     * Array defining the information about the current request path after matched against dispatcher
     * @var array
     */ 
	protected $routeInfo;

    /**
     * Array defining the information about the current request path after matched against dispatcher
     * @var Routes
     */ 
    protected $routes;

    /**
     * Current request
     * @var Request
     */ 
    protected $request;

    /**
     * View processor
     * @var View
     */ 
    protected $view;

    /**
     * Header response
     * @var Header
     */ 
	protected $header;

    /**
     * Current request url
     * @var string
     */ 
	protected $currentURL;

    /**
     * Route protected with CSFR Token
     * @var array
     */ 
    protected $protectedRoute = array('POST','DELETE', 'PATCH', 'PUT');

    /**
     * Description
     * @param Routes $routes 
     * @param Request $request 
     * @param App $app 
     * @param View $view 
     * @return Router
     */
    function __construct(Routes $routes,Request $request,App $app,View $view)
    {
        $this->routes = $routes;
        $this->request = $request;
        $this->app = $app;
        $this->view = $view;

        $this->header = new Header(); 

        return $this;
    }

    /**
     * Init the router
     */
	function init() 
	{
		$this->routes = $this->routes->getRouteCollections();
		
		$this->dispatcher = $this->createDispatcher();

		$this->currentURL = $this->request->getPathInfo();

		$this->dispatch(); 

	}

    /**
     * Create dispatcher for router
     */
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

    /**
     * Run the router, try to match current request path with registered route
     */
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
                    $this->app->get('whoops')->init();
               
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
		        	$this->sendResponse($routeHandler,$vars);
		        	return;
		        }

		        // Share routeHandler and vars to App
		        $this->app->share('routeHandler',$routeHandler);	
		        $this->app->share('routeHandlerVars',$vars);

		        // Defered route callback
		        add_action($routeHandler->getRunLevel(),function(){

		        	$routeHandler = $this->app->get('routeHandler');
		        	$vars = $this->app->get('routeHandlerVars');

		        	$this->sendResponse($routeHandler,$vars);
		        	
		        },$routeHandler->getPriority());

		        break;
		}

    }

    /**
     * Send response 
     * @param RouteHandler $routeHandler 
     * @param callable $vars 
     */
    protected function sendResponse(routeHandler $routeHandler,$vars)
    {
    	// Start buffering, handle any echo-ed content on routeHandler
        ob_start();
        
        // Handle request
        $response = $this->handle($routeHandler->getHandler(),$vars);

        // Transfer buffer to view
        $content = ob_get_contents();
        
        ob_end_clean();

        // Check if the response is Instance or SubClass of Symfony Response
        if(($response instanceof SymfonyResponse)||(is_subclass_of($response,'Symfony\Component\HttpFoundation\Response')))
        {
             // Set Buffer to View
            $this->view->setBuffer($content);                
        }else{
            // Set status header to 200
            $response = ResponseFactory::content($content);
        }

        $this->header->setResponse($response);
        $this->view->setResponse($response);
      
        // JSON Response
        if($response->isJson() || $routeHandler->isFastExit())
        {   
            $response->sendHeaders();
            $this->view->render();
            die();
        }

    }

    /**
     * Handle routeHandler Callable
     * @param RouteHandler $handler 
     * @param callable $vars 
     * @return type
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
       
            // The controller class was still not found. Throw Exception
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

