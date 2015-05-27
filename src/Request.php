<?php
namespace Tiga\Framework;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest 
{

	private $input = false;

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

}