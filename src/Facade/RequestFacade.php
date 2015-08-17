<?php

namespace Tiga\Framework\Facade;

/**
 * Request Facade.
 */
class RequestFacade extends Facade
{
    /**
      * Get the registered name of the component.
      *
      * @return string
      */
     public static function getFacadeAccessor()
     {
         return 'request';
     }
}
