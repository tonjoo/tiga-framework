<?php

namespace Tiga\Framework\Contract;

interface QueryCompilerInterface {

	function get();

	function lastQuery();

	function insert($data);

	function update($data);

	function compile($type);

}