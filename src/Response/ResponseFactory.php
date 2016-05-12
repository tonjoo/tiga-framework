<?php

namespace Tiga\Framework\Response;

use Symfony\Component\HttpFoundation\RedirectResponse as RedirectResponse;
use Tiga\Framework\Facade\ViewFacade as View;

/**
 * Factory class to generate response.
 */
class ResponseFactory
{
    /**
     * Generate string response.
     *
     * @param string $content
     * @param int    $status
     * @param array  $headers
     *
     * @return Response
     */
    public static function content($content, $status = 200, $headers = array())
    {
        return new Response($content, $status, $headers);
    }

    /**
     * Generate response from template.
     *
     * @param string $template
     * @param array  $parameter
     * @param int    $status
     * @param array  $headers
     *
     * @return Response
     */
    public static function template($template, $parameter = array(), $status = 200, $headers = array())
    {
        View::setTemplate($template, $parameter);

        return new Response('', $status, $headers);
    }

    /**
     * Return JSON response.
     *
     * @param array $data
     * @param int   $status
     * @param array $headers
     *
     * @return Response
     */
    public static function json($data, $status = 200, $headers = array())
    {
        $jsonHeader = array('Content-Type' => 'application/json');

        $content = json_encode($data);

        return new Response($content, $status, $jsonHeader);
    }

    /**
     * Redirect Response.
     *
     * @param string $url
     * @param int    $status
     * @param array  $headers
     */
    public static function redirect($url, $status = 302, $headers = array())
    {
        $redirect = new RedirectResponse($url, $status, $headers);
        $redirect->sendHeaders();
        die();
    }

    /**
     * Generate download file.
     *
     * @param string $file
     * @param int    $status
     * @param array  $headers
     */
    public static function download($file, $status = 200, $headers = array())
    {
        require_once ABSPATH.'wp-admin/includes/file.php';

        WP_Filesystem();

        global $wp_filesystem;

        $fileData = $wp_filesystem->get_contents($file);

        $downloadHeader['Content-Description'] = 'File Transfer';
        $downloadHeader['Content-Type'] = 'application/octet-stream';
        $downloadHeader['Content-Disposition'] = 'attachment; filename='.basename($file);
        $downloadHeader['Content-Transfer-Encoding'] = 'binary';
        $downloadHeader['Expires'] = '0';
        $downloadHeader['Cache-Control'] = 'must-revalidate';
        $downloadHeader['Pragma'] = 'public';
        $downloadHeader['Content-Length'] = filesize($file);

        $response = new Response($fileData, 200, $downloadHeader);
        $response->send();
    }
}
