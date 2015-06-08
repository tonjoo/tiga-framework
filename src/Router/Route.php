<?php 
namespace Tiga\Framework\Router;

class Route
{

	/**
	 * Method of the request
	 */
	private $method;

	/**
	 * Route 
	 */
	private $route;
	
    /**
	 * Route Handler
	 */
	private $routeHandler;


	public function __construct($method,$route,$routeHandler)
    {

		$this->method = $method;

		$this->route = $route;

		$this->routeHandler = $routeHandler;
	}


    /**
     * Gets the Method of the request.
     *
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    /**
     * Sets the Method of the request.
     *
     * @param mixed $method the method
     *
     * @return self
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Gets the Route.
     *
     * @return mixed
     */
    public function getRoute()
    {

        return $this->route;
    }
    
    /**
     * Gets the Converted Route.
     *
     * @return mixed
     */
    public function getConvertedRoute()
    {

        return $this->convertRouteParam($this->route);
    }
    
    /**
     * Sets the Route.
     *
     * @param mixed $route the route
     *
     * @return self
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Gets the routeHandler.
     *
     * @return mixed
     */
    public function getRouteHandler()
    {
        return $this->routeHandler;
    }
    
    /**
     * Sets the routeHandler.
     *
     * @param mixed $routeHandler the routeHandler
     *
     * @return self
     */
    public function setRouteHandler($routeHandler)
    {
        $this->routeHandler = $routeHandler;

        return $this;
    }

    public function convertRouteParam($route) 
    {

        $patterns = array(
            ':any?' => ':[a-zA-Z0-9\.\-_%=]?+', 
            ':num?' => ':[0-9]?+', 
            ':all?' => ':.?*',
            ':num' => ':[0-9]+', 
            ':any' => ':[a-zA-Z0-9\.\-_%=]+', 
            ':all' => ':.*',
        );

        $route = str_replace(array_keys($patterns), array_values($patterns), $route);

        return $route;
    }


    public function defer($runLevel,$priority=10)
    {
        $this->routeHandler->defer($runLevel,$priority);
    }

    /*
     * Exit execution after the controller is executed
     */
    public function end()
    {
        $this->routeHandler->fastExit();
    }
}