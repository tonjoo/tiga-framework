<?php

namespace Tiga\Framework\Contract;

interface ConnectionInterface
{
    public function getRow($query);

    public function getResult($query);

    public function quote($query);

    public function getPrefix();

    public function getInsertId();

    public function getRowsAffected();
}
