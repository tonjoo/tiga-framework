<?php

function tiga_url($url,$params=array()) {

	$url =  home_url( $url );

	return esc_url( add_query_arg( $params, $url ) );

}

function tiga_asset($path) {

	return plugins_url(Config::get('tiga.assets')."/".$path, dirname(TIGA_BASE_PATH) );

}
