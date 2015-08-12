<?php
namespace Tiga\Framework\Database;
use Tiga\Framework\Contract\QueryInterface as QueryInterface;
use Tiga\Exception\QueryExcepton as QueryExcepton;

class Query  {

	private $querySelect = array();

	private $queryWhere = array();

	private $queryWhereRaw = array();

	private $queryFrom = array(); 

	private $queryOrderBy = array();

	private $queryGroupBy = array();

	private $queryJoin = array();

	private $distinct = false;

	private $limit = false;

	private $offset = false;

	private $bind =  array();

	private $insert = false;

	private $update = false;

	function insert($insert) {
		$this->insert = $insert;
	} 

	function update($update) {
		$this->update = $update;
	}

	function getUpdate() {
		return $this->update;
	}

	function getInsert() {
		return $this->insert;
	}

	function bind($key,$value) {

		$this->bind[$key] = $value;

	}

	function getBind() {
		return $this->bind;
	}

	function offset($offset) {
		$this->offset = $offset;
	}

	function getOffset() {
		return $this->offset;
	}

	function limit($limit) {
		$this->limit = $limit;
	}

	function getLimit() {
		return $this->limit;
	}


	function getQuerySelect() {

		return $this->querySelect;
	
	}

	function getQueryWhere() {
	
		return $this->queryWhere;
	
	}

	function getQueryWhereRaw() {
	
		return $this->queryWhereRaw;
	
	}

	function getQueryFrom() {
	
		return $this->queryFrom;
	
	}

	function getQueryOrderBy() {
	
		return $this->queryOrderBy;
	
	}

	function getQueryGroupBy() {
	
		return $this->queryGroupBy;
	
	}

	function getQueryJoin() {
	
		return $this->queryJoin;
	
	}

	function table($table) {

		if(is_array($table))
			$this->queryFrom = array_merge($this->queryFrom, $table);
		else if(is_string($table))
			array_push($this->queryFrom, $table);

	}

	function where($where) {

		$condition = strtoupper($where['condition']);

		// Raw Query 
		if(array_key_exists('raw',$where)) {

			if($condition=='AND'){
				array_unshift($this->queryWhereRaw,$where);
			}
			else{
				array_push($this->queryWhereRaw,$where);
			}

			// End Here
			return;
		}

		
		if($condition=='AND'){
			array_unshift($this->queryWhere,$where);
		}
		else{
			array_push($this->queryWhere,$where);
		}

	}

	function orderBy($column,$order) {

		$stringOrderBy = "$column $order";
		
		if(is_string($stringOrderBy))
			array_push($this->queryOrderBy, $stringOrderBy);

	}

	function groupBy($column) {

		if(!is_array($column))
			$column = explode(',',$column);

		if(is_array($column))
			$this->queryGroupBy = array_merge($this->queryGroupBy, $column);
		
		if(is_string($column))
			array_push($this->queryGroupBy, $column);

	}

	function select($column) {

		if($column instanceof Raw) {

			array_push($this->querySelect, $column);
			return;

		}

		if(!is_array($column))
			$column = explode(',',$column);

		if(is_array($column))
			$this->querySelect = array_merge($this->querySelect, $column);
		else if(is_string($column))
			array_push($this->querySelect, $column);

	}

	function join($join){

		array_push($this->queryJoin,$join);

	}

	function distinct() {
		$this->distinct = true;
	}

	function reset() {
		$this->querySelect = array();
		$this->queryWhere = array();
		$this->queryWhereRaw = array();
		$this->queryFrom = array(); 
		$this->queryOrderBy = array();
		$this->queryGroupBy = array();
		$this->queryJoin = array();
		$this->distinct = false;
		$this->limit = false;
		$this->this = false;
	}
}
