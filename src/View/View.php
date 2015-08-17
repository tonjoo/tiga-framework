<?php

namespace Tiga\Framework\View;

use Tiga\Framework\Template\Template as Template;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 *  Handle view template loading and manipulation in WordPress.
 */
class View
{
    /**
     * Buffered string from route execution.
     *
     * @var string
     */
    protected $buffer;

    /**
     * WordPress page title.
     *
     * @var string
     */
    protected $title;

    /**
     * @var \Tiga\Framework\Response\Response
     */
    protected $response;

    /**
     * Template class to render php file.
     *
     * @var Template
     */
    protected $template;

    /**
     * Path to template file.
     *
     * @var string
     */
    protected $templatefile = false;

    /**
     * Template file params.
     */
    protected $templatefileParameters;

    /**
     * Contruct View class,.
     *
     * @param Template $template
     *
     * @return type
     */
    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
     *  Alter WordPress selected template instead it will be using the template selected by this class.
     */
    public function hook()
    {
        add_filter('template_include', array($this, 'overrideTemplate'), 10, 1);
    }

    /**
     * Set template file.
     *
     * @param string $templatefile
     * @param array  $templatefileParameters
     */
    public function setTemplate($templatefile, $templatefileParameters)
    {
        $this->templatefile = $templatefile;
        $this->templatefileParameters = $templatefileParameters;
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
     * Send content back.
     */
    public function sendResponse()
    {
        if ($this->response instanceof SymfonyResponse) {
            $this->response->sendContent();
        }
        if ($this->templatefile !== false) {
            echo $this->template->render($this->templatefile, $this->templatefileParameters);
        }
    }

    /**
     * Set buffer.
     *
     * @param string $buffer
     */
    public function setBuffer($buffer)
    {
        $this->buffer = $buffer;
    }

    /**
     * Get buffer.
     *
     * @return string
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * Render view.
     */
    public function render()
    {
        $view = $this;

        include TIGA_BASE_PATH.'vendor/tonjoo/tiga-framework/src/View/ViewGenerator.php';
    }

    /**
     * Implement the alteration of template perform by hook function.
     */
    public function overrideTemplate()
    {
        //Disable rewrite, lighter access for LF
        global $wp_rewrite;
        $wp_rewrite->rules = array();

        return __DIR__.'/ViewGenerator.php';
    }
}
