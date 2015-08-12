<?php

namespace Tiga\Framework\Database;

class Raw
{
    private $string;

    public function __construct($string)
    {
        $this->string = $string;

        return $this;
    }

    public function getString()
    {
        return $this->string;
    }
}
