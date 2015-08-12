<?php

namespace Tiga\Framework\Contract;

interface QueryInterface {

	function table($table);

	function where($column,$value,$operator,$condition);

	function orWhere($column,$value,$operator);

	function like($column,$value,$condition);

	function orLike($column,$value);

	function notLike($column,$value,$condition);

	function orNotLike($column,$value);

	function orderBy($column,$order); 

	function groupBy($column); 

	function select($columns); 

	function join($table,$leftColumn,$operator,$rightColumn,$joinType);

	function distinct();

	function limit($limit);

	function offset($offset);

	function reset();

	function bind($key,$value);

	function quote($value);

	function insert($table,$data);

	function update($data);

	function get();

	function row();

	function getInsertId();

	function delete();

}