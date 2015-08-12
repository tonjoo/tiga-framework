<?php

namespace Tiga\Framework\Facade;

/**
 * Form Facade.
 */
class FormFacade extends Facade
{
    /**
      * Get the registered name of the component.
      *
      * @return string
      */
     public static function getFacadeAccessor()
     {
         return 'form';
     }
}
