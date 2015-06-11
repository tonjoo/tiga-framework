<?php
namespace Tiga\Framework\View;
use Tiga\Framework\Template\Template as Template;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class View 
{
	
	protected $buffer;
	protected $title;
	
	protected $response;

	protected $template;

	protected $templatefile = false;	
	protected $templatefileParameters;

	public function __construct(Template $template) 
	{	
		$this->template = $template;		
	}

	public function hook()
	{
		add_filter('template_include', array($this,'overrideTemplate'),10,1);	
	}
	
	public function setTemplate($templatefile,$templatefileParameters) 
	{
		$this->templatefile = $templatefile;
		$this->templatefileParameters = $templatefileParameters;
	}

	public function setResponse($response)
	{
		$this->response = $response;
	}

	public function sendResponse()
	{
		if($this->response instanceof SymfonyResponse){
        	$this->response->sendContent();
        }
        if($this->templatefile!==false){
        	echo $this->template->render($this->templatefile,$this->templatefileParameters);
        }
	}
	
	public function setBuffer($buffer) 
	{
		$this->buffer = $buffer;
	}
	
	public function getBuffer() 
	{		
		return $this->buffer;
	}

	public function render()
	{
		$view = $this;

		include TIGA_BASE_PATH.'vendor/tonjoo/tiga-framework/src/View/ViewGenerator.php';
	}

	public function overrideTemplate() 
	{
		//Disable rewrite, lighter access for LF
		global $wp_rewrite;
		$wp_rewrite->rules = array();
		return __DIR__.'/ViewGenerator.php';
	}
}