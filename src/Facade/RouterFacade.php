<?php

namespace Tiga\Framework\Facade;

/**
 * Router Facade.
 */
class RouterFacade extends Facade
{
    /**
      * Get the registered name of the component.
      *
      * @return string
      */
     public static function getFacadeAccessor()
     {
         return 'router';
     }
}
