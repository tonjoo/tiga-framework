<?php

namespace Tiga\Framework\ServiceProvider;

/**
 *  Whoops service provider.
 */
class WhoopsServiceProvider extends AbstractServiceProvider
{
    public function register()
    {
        $this->app['whoops'] = new \Tiga\Framework\Whoops($this->app);
    }
}
