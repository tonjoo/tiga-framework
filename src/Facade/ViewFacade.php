<?php

namespace Tiga\Framework\Facade;

/**
 * View Facade.
 */
class ViewFacade extends Facade
{
    /**
      * Get the registered name of the component.
      *
      * @return string
      */
     public static function getFacadeAccessor()
     {
         return 'view';
     }
}
