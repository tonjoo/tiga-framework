<?php 
namespace Tiga\Framework\Html;

use Tiga\Framework\Request;
use Tiga\Framework\Contract\OldInputInterface;

/**
 * Form flash handler
 */ 
class FlashFormOldInput implements OldInputInterface
{
    /**
     * @var array
     */ 
    private $input = false;   

     /**
     * @var Request
     */ 
    private $request = false;

    /**
     * Constructor
     * @param Request Request $request 
     * @return type
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Check if flash contain old input
     * @return boolean
     */
    public function hasOldInput()
    {
        return $this->request->hasOldInput() ;
    }   

    /**
     * Get old input by key
     * @param string $key 
     * @return mixed
     */
    public function getOldInput($key)
    {
        $this->input = $this->request->hasOldInput()!==false ? $this->request->oldInput() : array() ;

        // Input that is flashed to the flash can be easily retrieved by the
        // developer, making repopulating old forms and the like much more
        // convenient, since the request's previous input is available.
        return array_get($this->input, $this->transformKey($key),null);

    }

    /**
     * Transform key
     * @param string $key 
     * @return string
     */
    protected function transformKey($key)
    {
        return str_replace(array('.', '[]', '[', ']'), array('_', '', '.', ''), $key);
    }
}
