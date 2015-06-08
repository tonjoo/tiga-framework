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

	// @todo : hook bagian lain dari page
	// add_action('pre_get_posts', array($this, 'edit_query'), 10, 1);
	// add_action('the_post', array($this, 'set_post_contents'), 10, 1);
	// add_filter('the_title', array($this, 'get_title'), 10, 2);
	// add_filter('single_post_title', array($this, 'get_single_post_title'), 10, 2);
	// add_filter('redirect_canonical', array($this, 'override_redirect'), 10, 2);
	// add_filter('get_post_metadata', array($this, 'set_post_meta'), 10, 4);
	// add_filter('post_type_link', array($this, 'override_permalink'), 10, 4);
	// if ( $this->template ) {
	//     add_filter('template_include', array($this, 'override_template'), 10, 1);
	// }
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