<?php
namespace Tiga\Framework\View;
use Tiga\Template\Template;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class View 
{
	
	protected $buffer;

	protected $title;
	
	protected $response;

	protected $template = false;
	
	protected $templateParameters;

	function __construct(Template $template) 
	{
		$this->template = $template;
		add_filter('template_include', array($this,'overrideTemplate'),10,1);	
	}

	function setTemplate($template,$templateParameters) 
	{
		$this->template = $template;
		$this->templateParameters = $templateParameters;
	}

	function setResponse($response)
	{
		$this->response = $response;
	}

	function sendResponse()
	{
		if($this->response instanceof SymfonyResponse){
        	$this->response->sendContent();
        }

        if($this->template!==false){
        	echo $this->template->render($this->template,$this->templateParameters);
        }
	}

	function setBuffer($buffer) 
	{
		$this->buffer = $buffer;
	}

	function getBuffer() 
	{		
		return $this->buffer;
	}

	public function overrideTemplate() 
	{
		//Disable rewrite, lighter access for LF
		global $wp_rewrite;

		$wp_rewrite->rules = array();

		return __DIR__.'/ViewGenerator.php';
	}
}

