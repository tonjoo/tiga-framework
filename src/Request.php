<?php
namespace Tiga\Framework;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Tiga\Framework\Facade\ResponseFactoryFacade as Response;

class Request extends SymfonyRequest 
{

	protected $flash;

	protected $oldInput = false;

	private $input = false;

	protected $token =false;
 
	/**
	 * Determine if the request is sending JSON.
	 *
	 * @return bool
	 */
	public function isJson()
	{
		$header = $this->headers->get('CONTENT_TYPE');

		if (strpos($header, 'json') !== false)
    		return true;

    	return false;
	}

	private function populateInput() 
	{
		// If already populate, return
		if($this->input)
			return;
		
		if($this->isJson()) 
		{
			$json = new ParameterBag((array) json_decode($this->getContent(), true));
			$this->input = $json->all();
		}
		// GET
		$get = $this->query->all();
		// POST 
		$post = $this->request->all() ;
		$this->input = array_merge($get,$post);
	}
	

	public function input($key,$default=false) 
	{

		$this->populateInput();

		if(isset($this->input[$key]))
			return $this->input[$key];

		return $default;
	}

	public function exclude($key) 
	{

		$this->populateInput();

		if(array_key_exists($key, $this->input))
			unset($this->input[$key]);

		return $this->input;
	}

	public function has($key) 
	{

		$this->populateInput();

		return array_key_exists($key,$this->input);
	}

	public function all() 
	{

		$this->populateInput();

		return $this->input;
	}

	public function setFlash($flash)
	{
		$this->flash = $flash;
	}

	public function flash()
	{
		$this->flash->set('_old_input',$this->all());
	}

	public function hasOldInput()
	{
		return  $this->flash->get('_old_input',false);
	}

	protected function populateOldInput()
	{
		if($this->flash->has('_old_input'))
		{
			$this->oldInput = $this->flash->get('_old_input');
		}
	}

	public function oldInput($name=false)
	{
		return $this->flash->get('_old_input',false);
	}


	public function checkToken()
	{
		
		if($this->flash->get('tiga_csrf_token',false)==false)
			$this->killRequest("Invalid csrf token");

		if($this->flash->get('tiga_csrf_token') != $this->input('_token'))
			$this->killRequest("Invalid csrf token");
	}

	public function killRequest($message)
	{
		if($this->isJson())
			$response = Response::json(array("content"=>$message),501);
		else
			$response = Response::content($message,501);

		$response->send();
		die();

	}

}