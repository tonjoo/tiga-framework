<?php

namespace Tiga\Framework\Facade;

/**
 * HTML Facade.
 */
class HtmlFacade extends Facade
{
    /**
      * Get the registered name of the component.
      *
      * @return string
      */
     public static function getFacadeAccessor()
     {
         return 'html';
     }
}
