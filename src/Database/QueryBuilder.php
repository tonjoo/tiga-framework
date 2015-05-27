<?php

namespace Tiga\Framework\Database;
use Tiga\Framework\Contract\QueryInterface as QueryInterface;
use Tiga\Framework\Contract\QueryCompilerInterface as QueryCompilerInterface;
use Tiga\Framework\Database\Raw as Raw;

class QueryBuilder implements QueryInterface {

	private $queryCompiler;

	private $connection;

	function __construct($queryCompiler,$connection) {

		$this->queryCompiler = $queryCompiler;

		$this->connection = $connection;


		return $this;

	}

	function getConnection() {

		return $this->connection;

	}


	function setResultType($result_type) {

		$this->connection->setResultType($result_type) ;

		return $this;
	}

	/*
	 * Set Query
	 */
	function query($query) {

		$this->rawQuery = true;

		$this->queryCompiler->setQuery($query);

		return $this;
	}

	/* 
	 * Get Result
	 */
	function get() {

		$this->queryCompiler->compile();

		return $this->getResult('get');
		
	}


	function row() {

		$this->queryCompiler->compile();

		return $this->getResult('row');
	}

	// Alias of row
	function execute() {

		return $this->row();
	}


	private function getResult($type='row') {

		$query = $this->queryCompiler->getCompiledQuery();

		if($type=='row')
			$result = $this->connection->getRow($query);
		elseif($type=='get')
			$result = $this->connection->getResult($query);
		// Insert
		$queryType = ltrim($query);

		$queryType = substr($query,0,8);

		$queryType = strtoupper($queryType);

		if(strpos($query, "INSERT") !== false) {
			
			if($result!==false)
				return $this->connection->getInsertId();

			return $result;
		}
		// Update Delete
		if(strpos($query, "UPDATE") !== false || strpos($query, "DELETE") !== false 
			|| strpos($query, "DROP") !== false || strpos($query, "CREATE") !== false) {
			if($result!==false)
				return $this->connection->getRowsAffected();

			return false;
		}
		
		// Select
		return $result;
	}

	/*
	 * Return False on error, ID on success
	 */
	function insert($table,$data=false) {

		if($data==false)
			$data=$table;
		else
			$this->table($table);

		// Escape SQL 
		foreach ($data as $key => $value) {
			$data[$key] = $this->quote($value);
		}

		$this->queryCompiler->getQuery()->insert($data);

		$this->queryCompiler->compile("insert");

		return $this->getResult('get');
		
	}

	/*
	 * Return Affected row success, false on error
	 */
	function update($table,$data=false) {

		if($data==false)
			$data=$table;
		else
			$this->table($table);

		// Escape SQL 
		foreach ($data as $key => $value) {
			$data[$key] = $this->quote($value);
		}

		$this->queryCompiler->getQuery()->update($data);

		$this->queryCompiler->compile("update");

		return $this->getResult('get');

	}

	/*
	 * Return Affected row success, false on error
	 */
	function delete() {
		
		$this->queryCompiler->compile("delete");

		return $this->getResult('get');

	}

	function count() {

		$this->queryCompiler->compile("count");

		$result = $this->connection->getRow($this->queryCompiler->getCompiledQuery());

		return $result->count;
	}


	/*  
	 * Implement Query Interface
	 */
	function where($column,$operator='=',$value=false,$condition='AND') {

		$where = array();


		// Raw Query
		if($column instanceof Raw) {
			
			$where['raw'] = true;
			$where['query'] = $column->getString(); 
			$where['condition'] = $condition; 
			$where['operator'] = $operator; 

			$this->queryCompiler->getQuery()->where($where);
			
			return $this;
		}

		
		$where['column'] = $column; 
		$where['operator'] = $operator; 
		$where['value'] = $this->quote($value); 
		$where['condition'] = $condition; 

		$this->queryCompiler->getQuery()->where($where);

		return $this;
	}

	function orWhere($column,$operator='=',$value) {


		$where = array();
		$where['column'] = $column; 
		$where['operator'] = $operator; 
		$where['value'] = $this->quote($value);
		$where['condition'] = "OR"; 
		
		$this->queryCompiler->getQuery()->where($where);

		return $this;
	}

	function like($column,$value,$condition='AND'){

		$where = array();
		$where['column'] = $column; 
		$where['operator'] = "LIKE"; 
		$where['value'] = $this->quote($value); 
		$where['condition'] = $condition; 
		
		$this->queryCompiler->getQuery()->where($where);

		return $this;
	}

	function orLike($column,$value){

		$where = array();
		$where['column'] = $column; 
		$where['operator'] = "LIKE"; 
		$where['value'] = $this->quote($value);
		$where['condition'] = "OR"; 

		$this->queryCompiler->getQuery()->where($where);

		return $this;
	
	}

	function notLike($column,$value,$condition){


		$where = array();
		$where['column'] = $column; 
		$where['operator'] = "NOT LIKE"; 
		$where['value'] = $this->quote($value);
		$where['condition'] = $condition; 

		$this->queryCompiler->getQuery()->where($where);

		return $this;

	}

	function orNotLike($column,$value){

		$where = array();
		$where['column'] = $column; 
		$where['operator'] = "NOT LIKE"; 
		$where['value'] = $this->quote($value); 
		$where['condition'] = "OR"; 

		$this->queryCompiler->getQuery()->where($where);

		return $this;

	}

	function orderBy($column,$order) {

		$this->queryCompiler->getQuery()->orderBy($column,$order);

		return $this;

	}

	function groupBy($column) {

		$this->queryCompiler->getQuery()->groupBy($column);

		return $this;

	}

	function select($column) {

		$this->queryCompiler->getQuery()->select($column);

		return $this;
	}

	function join($table,$leftColumn='',$operator='=',$rightColumn='',$joinType=''){

		if($table instanceof Raw) {
			
			$join['join'] = $table;  
			$join['raw'] = true; 

			$this->queryCompiler->getQuery()->join($join);;

			return $this;
		}


		$join = array();
		$join['table'] = $table; 
		$join['leftColumn'] = $leftColumn; 
		$join['operator'] = $operator; 
		$join['rightColumn'] = $rightColumn; 
		$join['joinType'] = $joinType; 
		$join['raw'] = false; 

		$this->queryCompiler->getQuery()->join($join);;

		return $this;
	}

	function table($table) {
		
		$this->queryCompiler->getQuery()->table($table);

		return $this;
	
	}
	/* 
	 * Alias of Table
	 */
	function from($table) {
		
		return $this->table($table);
	
	}

	function distinct() {
		$this->queryCompiler->getQuery()->distinc();

		return $this;
	}

	/*
	 * Implement QueryCompilerInterface
	 */
	
	function getCompiledQuery() {
	
		return $this->queryCompiler->getCompiledQuery();
	
	}

	function lastQuery() {
	
		return $this->queryCompiler->lastQuery();
	
	}


	function offset($offset) {

		$this->queryCompiler->getQuery()->offset(intval($offset));

		return $this;
	}

	function limit($limit) {

		$this->queryCompiler->getQuery()->limit(intval($limit));

		return $this;
	}

	function reset() {
		$this->queryCompiler->getQuery()->reset();
	}

	function bind($key,$value = false) {

		if(is_array($key)) {

			foreach ($key as $keyAr => $value) {
				$this->queryCompiler->getQuery()->bind($keyAr,$this->quote($value));
			}
				
			return $this;
		}

		$this->queryCompiler->getQuery()->bind($key,$this->quote($value));

		return $this;
	}


	function quote($value) {
		return $this->connection->quote($value);
	}

	function getInsertId() {
		return $this->connection->getInsertId();
	}

	function raw($string) {

		return new Raw($string);
	}

}

