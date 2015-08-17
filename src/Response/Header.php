<?php

namespace Tiga\Framework\Response;

/**
 * HTTP Header class.
 */
class Header
{
    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Response.
     */
    protected $response;

    /**
     * Create header class and init the hook.
     */
    public function __construct()
    {
        $this->hook();

        return $this;
    }

    /**
     * Set response instance.
     *
     * @param Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Hook this class into status_header.
     */
    public function hook()
    {
        add_filter('status_header', array($this, 'sendHeaderResponse'), 100);
    }

    /**
     * Send proper HTTP response.
     */
    public function sendHeaderResponse()
    {
        if ($this->response != false) {
            $this->response->sendHeaders();

            return $this->response->getWpStatusCodeHeader();
        }
    }
}
