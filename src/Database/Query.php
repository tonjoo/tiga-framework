<?php

namespace Tiga\Framework\Database;

class Query
{
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

    private $bind = array();

    private $insert = false;

    private $update = false;

    public function insert($insert)
    {
        $this->insert = $insert;
    }

    public function update($update)
    {
        $this->update = $update;
    }

    public function getUpdate()
    {
        return $this->update;
    }

    public function getInsert()
    {
        return $this->insert;
    }

    public function bind($key, $value)
    {
        $this->bind[$key] = $value;
    }

    public function getBind()
    {
        return $this->bind;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getQuerySelect()
    {
        return $this->querySelect;
    }

    public function getQueryWhere()
    {
        return $this->queryWhere;
    }

    public function getQueryWhereRaw()
    {
        return $this->queryWhereRaw;
    }

    public function getQueryFrom()
    {
        return $this->queryFrom;
    }

    public function getQueryOrderBy()
    {
        return $this->queryOrderBy;
    }

    public function getQueryGroupBy()
    {
        return $this->queryGroupBy;
    }

    public function getQueryJoin()
    {
        return $this->queryJoin;
    }

    public function table($table)
    {
        if (is_array($table)) {
            $this->queryFrom = array_merge($this->queryFrom, $table);
        } elseif (is_string($table)) {
            array_push($this->queryFrom, $table);
        }
    }

    public function where($where)
    {
        $condition = strtoupper($where['condition']);

        // Raw Query 
        if (array_key_exists('raw', $where)) {
            if ($condition == 'AND') {
                array_unshift($this->queryWhereRaw, $where);
            } else {
                array_push($this->queryWhereRaw, $where);
            }

            // End Here
            return;
        }

        if ($condition == 'AND') {
            array_unshift($this->queryWhere, $where);
        } else {
            array_push($this->queryWhere, $where);
        }
    }

    public function orderBy($column, $order)
    {
        $stringOrderBy = "$column $order";

        if (is_string($stringOrderBy)) {
            array_push($this->queryOrderBy, $stringOrderBy);
        }
    }

    public function groupBy($column)
    {
        if (!is_array($column)) {
            $column = explode(',', $column);
        }

        if (is_array($column)) {
            $this->queryGroupBy = array_merge($this->queryGroupBy, $column);
        }

        if (is_string($column)) {
            array_push($this->queryGroupBy, $column);
        }
    }

    public function select($column)
    {
        if ($column instanceof Raw) {
            array_push($this->querySelect, $column);

            return;
        }

        if (!is_array($column)) {
            $column = explode(',', $column);
        }

        if (is_array($column)) {
            $this->querySelect = array_merge($this->querySelect, $column);
        } elseif (is_string($column)) {
            array_push($this->querySelect, $column);
        }
    }

    public function join($join)
    {
        array_push($this->queryJoin, $join);
    }

    public function distinct()
    {
        $this->distinct = true;
    }

    public function reset()
    {
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
