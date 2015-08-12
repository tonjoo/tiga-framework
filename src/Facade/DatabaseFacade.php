<?php

namespace Tiga\Framework\Facade;

/**
 * Database facade.
 */
class DatabaseFacade extends Facade
{
    /**
      * Get the registered name of the component.
      *
      * @return string
      */
     public static function getFacadeAccessor()
     {
         return 'db';
     }
}
