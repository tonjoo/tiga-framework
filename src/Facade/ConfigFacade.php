<?php

namespace Tiga\Framework\Facade;

/**
 * Config facade.
 */
class ConfigFacade extends Facade
{
    /**
      * Get the registered name of the component.
      *
      * @return string
      */
     public static function getFacadeAccessor()
     {
         return 'config';
     }
}
