<?php
namespace Tiga\Framework\ServiceProvider;

class AjaxServiceProvider extends AbstractServiceProvider
{
	public function register()
	{	

		$ajax = new \Tiga\Framework\Ajax();

		$ajax->hook();

	}
}