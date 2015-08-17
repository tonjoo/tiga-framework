<?php

namespace Tiga\Framework\Response;

/**
 * Class to handle response.
 */
class Response extends \Symfony\Component\HttpFoundation\Response
{
    /*
     * Check if response is JSON
     */
    public function isJson()
    {
        return $this->headers->get('Content-Type') == 'application/json';
    }

    /**
     * Format response for WordPress.
     */
    public function getWpStatusCodeHeader()
    {
        return sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText);
    }
}
