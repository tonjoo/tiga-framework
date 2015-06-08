<?php
/*
 * Plugin specific function
 */
function tiga_url($url,$params=array()) {

	$url =  home_url( $url );

	return esc_url( add_query_arg( $params, $url ) );

}

function tiga_asset($path) {

	return plugins_url(Config::get('tiga.assets')."/".$path, dirname(TIGA_BASE_PATH) );
}

/*
 * General Tiga Framework Helper
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

function array_set($arr,$path, $value) 
{    
   $loc = &$arr;
   foreach(explode('.', $path) as $step)
   {
     $loc = &$loc[$step];
   }

   return $loc = $value;
}

function csrf_token()
{
	return Form::getToken();
}