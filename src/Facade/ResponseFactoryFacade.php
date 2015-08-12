<?php

namespace Tiga\Framework\Facade;

/**
 * Response Factory Factory.
 */
class ResponseFactoryFacade extends Facade
{
    /**
      * Get the registered name of the component.
      *
      * @return string
      */
     public static function getFacadeAccessor()
     {
         return 'responseFactory';
     }
}
