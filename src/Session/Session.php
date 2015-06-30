<?php

namespace Tiga\Framework\Session;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Tiga\Framework\Session\WPSessionHandler;

/**
 * Session handler
 */
class Session extends SymfonySession{

	/**
	 * Get the requested item from the flashed input array.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function getOldInput($key = null, $default = null)
	{
		$input = $this->get('_old_input', array());

		// Input that is flashed to the session can be easily retrieved by the
		// developer, making repopulating old forms and the like much more
		// convenient, since the request's previous input is available.
		return array_get($input, $key, $default);
	}


}