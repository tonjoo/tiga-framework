<?php

namespace Tiga\Framework\Response;
use Symfony\Component\HttpFoundation\RedirectResponse as RedirectResponse;
use Tiga\Framework\Facade\TemplateFacade as Template;
use Tiga\Framework\Facade\ViewFacade as View;

class ResponseFactory {

	public static function content($content,$status,$headers) {
	
		return new Response($content,$status,$headers);	
	}

	public static function template($template,$parameter=array(),$status=200,$headers=array()) {

		View::setTemplate($template,$parameter);

		return new Response('',$status,$headers);

	}

	public static function json($data,$status=200,$headers=array()) {

		$jsonHeader = array('Content-Type' =>'application/json');

		$content = json_encode($data);

		return new Response($content,200,$jsonHeader);

	}

	public static function redirect($url,$status=302,$headers=array()) {

		$redirect = new RedirectResponse($url,$status,$headers);
		$redirect->sendHeaders();
		die();

	}

	public static function download($file,$status=200,$headers=array()) {

		require_once ABSPATH."wp-admin/includes/file.php";

		WP_Filesystem();

   		global $wp_filesystem;

    	$fileData = $wp_filesystem->get_contents( $file );

		$downloadHeader["Content-Description"]= "File Transfer";
    	$downloadHeader["Content-Type"]= "application/octet-stream";
    	$downloadHeader["Content-Disposition"]= "attachment; filename=".basename($file);
    	$downloadHeader["Content-Transfer-Encoding"]= "binary";
    	$downloadHeader["Expires"]= "0";
    	$downloadHeader["Cache-Control"]= "must-revalidate";
    	$downloadHeader["Pragma"]= "public";
    	$downloadHeader["Content-Length"]= filesize($file);

		$response =  new Response($fileData,200,$downloadHeader);
		$response->send();
	}
}