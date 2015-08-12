<?php
/**
 * Get Tiga url from a route
 * @param string $url 
 * @param array $params 
 * @return string
 */
function tiga_url($url,$params=array()) {

	$url =  home_url( $url );

	return esc_url( add_query_arg( $params, $url ) );

}

/**
 * Get Tiga asset url from given path relative to assets folder defined in config
 * @param string $path 
 * @return string
 */
function tiga_asset($path) {

	return plugins_url(Config::get('tiga.assets')."/".$path, dirname(TIGA_BASE_PATH) );
}

/**
 * Get array value using . notation
 * @param array $arr 
 * @param string $path 
 * @param mixed $value 
 * @return mixed
 */
function array_get($arr,$path, $value = null) 
{
	$loc = &$arr;
	   
	foreach(explode('.', $path) as $step)
	{
	    if(!isset($loc[$step]))
	   	 	return $value;

	    $loc = &$loc[$step];

	}
	   
 return $loc;
}

/**
 * Set array using . notation
 * @param array $arr 
 * @param string $path 
 * @param string $value 
 * @return boolean
 */
function array_set($arr,$path, $value) 
{    
   $loc = &$arr;
   foreach(explode('.', $path) as $step)
   {
     $loc = &$loc[$step];
   }

   return $loc = $value;
}

/**
 * Print csrf token meta
 */
function csrf_token()
{
	return Form::getToken();
}