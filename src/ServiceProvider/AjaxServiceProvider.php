<?php

namespace Tiga\Framework\ServiceProvider;

/**
 *  Ajax helper service provider.
 */
class AjaxServiceProvider extends AbstractServiceProvider
{
    public function register()
    {
        $ajax = new \Tiga\Framework\Ajax();

        $ajax->hook();
    }
}
