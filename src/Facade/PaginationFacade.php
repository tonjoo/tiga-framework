<?php

namespace Tiga\Framework\Facade;

/**
 * Pagination Facade.
 */
class PaginationFacade extends Facade
{
    /**
      * Get the registered name of the component.
      *
      * @return string
      */
     public static function getFacadeAccessor()
     {
         return 'pagination';
     }
}
