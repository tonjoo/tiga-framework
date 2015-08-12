<?php
namespace Tiga\Framework\Database;
use Tiga\Framework\Contract\ConnectionInterface as ConnectionInterface;
use Tiga\Framework\Exception\DatabaseException as DatabaseException;

class WPDBConnection implements ConnectionInterface {

	private $wpdb;

	private $resultType; 

	function __construct() {

		global $wpdb;

		$this->wpdb = $wpdb;

		$this->resultType = OBJECT;
		
	}

	function getRow($query) {
		
		$result  =  $this->wpdb->get_row($query,$this->resultType);

		if($this->wpdb->last_error!=='')
			throw new DatabaseException($query);

		return $result;
	}

	function getResult($query) {
		$result =  $this->wpdb->get_results($query,$this->resultType);

		if($this->wpdb->last_error!=='')
			throw new DatabaseException($query);
			
		return $result;
	}

	function quote($string) {
		
		return "'".esc_sql($string)."'";;

	}

	function getPrefix() {
		
		return $this->wpdb->prefix;

	}

	function getInsertId() {
		return $this->wpdb->insert_id;
	}

	function setResultType($resultType) {
		$expected_type = array(ARRAY_A,ARRAY_N,OBJECT);

		if(!in_array($resultType, $expected_type)){
			$this->resultType = OBJECT;
		}

		$this->resultType  = $resultType;
	}

	function getRowsAffected() {
		return $this->wpdb->rows_affected;
	}

}