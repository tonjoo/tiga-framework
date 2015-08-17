<?php

namespace Tiga\Framework\Contract;

interface QueryCompilerInterface
{
    public function get();

    public function lastQuery();

    public function insert($data);

    public function update($data);

    public function compile($type);
}
