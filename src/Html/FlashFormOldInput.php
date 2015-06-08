<?php 
namespace Tiga\Framework\Html;

use Tiga\Framework\Request;
use Tiga\Framework\Contract\OldInputInterface;

class FlashFormOldInput implements OldInputInterface
{

    private $input = false;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function hasOldInput()
    {
        return $this->request->hasOldInput() ;
    }

    public function getOldInput($key)
    {
        $this->input = $this->request->hasOldInput()!==false ? $this->request->oldInput() : array() ;

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
