<?php

namespace Tiga\Framework\Database;

use Tiga\Framework\Contract\ConnectionInterface as ConnectionInterface;
use Tiga\Framework\Exception\DatabaseException as DatabaseException;

class WPDBConnection implements ConnectionInterface
{
    private $wpdb;

    private $resultType;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;

        $this->resultType = OBJECT;
    }

    public function getRow($query)
    {
        $result = $this->wpdb->get_row($query, $this->resultType);

        if ($this->wpdb->last_error !== '') {
            throw new DatabaseException($query);
        }

        return $result;
    }

    public function getResult($query)
    {
        $result = $this->wpdb->get_results($query, $this->resultType);

        if ($this->wpdb->last_error !== '') {
            throw new DatabaseException($query);
        }

        return $result;
    }

    public function quote($string)
    {
        return "'".esc_sql($string)."'";
    }

    public function getPrefix()
    {
        return $this->wpdb->prefix;
    }

    public function getInsertId()
    {
        return $this->wpdb->insert_id;
    }

    public function setResultType($resultType)
    {
        $expected_type = array(ARRAY_A,ARRAY_N,OBJECT);

        if (!in_array($resultType, $expected_type)) {
            $this->resultType = OBJECT;
        }

        $this->resultType = $resultType;
    }

    public function getRowsAffected()
    {
        return $this->wpdb->rows_affected;
    }
}
