<?php 
namespace Tiga\Framework\Html;

use Tiga\Framework\Session\Flash;
use Tiga\Framework\Contract\OldInputInterface;

class FlashFormOldInput implements OldInputInterface
{
    private $flash;

    private $input = false;

    public function __construct(Flash $flash)
    {
        $this->flash = $flash;
    }

    public function hasOldInput()
    {
        return $this->flash->has('_old_input') ;
    }

    public function getOldInput($key)
    {

        if(!$this->input)
            $this->input = $this->flash->get('_old_input', array());

        // Input that is flashed to the flash can be easily retrieved by the
        // developer, making repopulating old forms and the like much more
        // convenient, since the request's previous input is available.
        return array_get($this->input, $this->transformKey($key),null);

    }

    protected function transformKey($key)
    {
        return str_replace(array('.', '[]', '[', ']'), array('_', '', '.', ''), $key);
    }
}
