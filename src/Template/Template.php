<?php 
namespace Tiga\Framework\Template;

include "h2o.php";

use App;

class Template 
{
	private $config;

	private $engine=false;

	function __construct($config) 
	{
		// Base path location for H20 template
		$this->config = $config;		
	}

	private function initH2o() 
	{

		// Do nothing if h2o engine is already iniziated
		if($this->engine)
			return;

		// Configure H20 cache folder
		if(isset($this->config['storage']))
		{
			if(!file_exists($this->config['storage']))
				throw new \Exception("Storage folder on {$storage} does not exist");

			if(!is_writable($this->config['storage']))
			{
				if(!chmod($this->config['storage'],0777))
					throw new \Exception("Storage folder on $storage is not writable");
			}
		}		

		// Create ready to use H20 Engine
		$this->engine = new \H2o(null, array(
		    'searchpath' => $this->config['path'],
		    'cache'=>false
		));
	}

	private function renderH20($template,$parameter=array()) 
	{
		$this->initH2o();
		
		$this->engine->loadTemplate($template);

		$content = $this->engine->render($parameter);

		return $content;
	}

	private function renderPhp($template,$parameter=array()) 
	{
		$finalPath = false;
		//Final Path
		foreach ($this->config['path'] as $path) {
			if(file_exists($path.$template))
				$finalPath = $path.$template;
		}

		if(!$finalPath)
			throw new \Exception("Template : '$template' not found");
		

		foreach ($parameter as $key => $value) {
			${$key} = $value;
		}

		include $finalPath;		
	}

	public function render($template,$parameter=array()) 
	{
		if(stripos($template,".template")) 
			return $this->renderH20($template,$parameter);
		
		return $this->renderPhp($template,$parameter);
	}

	function hookTitle( $title,$sep ) 
	{
		return $this->title;;
	}
	
	function setTitle($title) 
	{
		$this->title = $title;

		add_filter( 'wp_title', array($this,'hookTitle'), 10, 2 );
	}

}