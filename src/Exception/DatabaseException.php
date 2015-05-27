<?php namespace Tiga\Framework\Exception;

class DatabaseException extends \Exception 
{

	public function __construct($query = false) 
	{
		global $wpdb;

		$error = "{$wpdb->last_error}. SQL Query : ";		
		if($query)
			$error .= '"'.$query.'"';
		else
			$error .= '"'.$wpdb->last_query.'"';

		parent::__construct($error);
	}

}