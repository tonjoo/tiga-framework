<?php

namespace Tiga\Framework\Facade;

/**
 * Flash Facade.
 */
class FlashFacade extends Facade
{
    /**
      * Get the registered name of the component.
      *
      * @return string
      */
     public static function getFacadeAccessor()
     {
         return 'flash';
     }
}
