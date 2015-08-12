<?php

namespace Tiga\Framework\Facade;

/**
 * Validator Facade.
 */
class ValidatorFacade extends Facade
{
    /**
      * Get the registered name of the component.
      *
      * @return string
      */
     public static function getFacadeAccessor()
     {
         return 'validator';
     }
}
