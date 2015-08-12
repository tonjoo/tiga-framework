<?php

namespace Tiga\Framework\Contract;

interface QueryInterface
{
    public function table($table);

    public function where($column, $value, $operator, $condition);

    public function orWhere($column, $value, $operator);

    public function like($column, $value, $condition);

    public function orLike($column, $value);

    public function notLike($column, $value, $condition);

    public function orNotLike($column, $value);

    public function orderBy($column, $order);

    public function groupBy($column);

    public function select($columns);

    public function join($table, $leftColumn, $operator, $rightColumn, $joinType);

    public function distinct();

    public function limit($limit);

    public function offset($offset);

    public function reset();

    public function bind($key, $value);

    public function quote($value);

    public function insert($table, $data);

    public function update($data);

    public function get();

    public function row();

    public function getInsertId();

    public function delete();
}
