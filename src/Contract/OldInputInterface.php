<?php 
namespace Tiga\Framework\Contract;

interface OldInputInterface
{
    public function hasOldInput();
    public function getOldInput($key);
}
