<?php

namespace Zeizig\Moodle\Globals;

/**
 * Class Page.
 * Wrapper for the Moodle global variable $PAGE.
 *
 * @package Zeizig\Moodle\Globals
 */
class Page
{
    /** @var \StdClass */
    protected $page;

    /**
     * Page constructor.
     */
    public function __construct()
    {
        global $PAGE;
        $this->page = $PAGE;
    }

    /**
     * Add a breadcrumb. If link is set will add it as a link, if not, only text will be used.
     *
     * @param  string       $text Text of the breadcrumb
     * @param  string|null  $link Link if needed
     *
     * @return void
     */
    public function addBreadcrumb($text, $link = null)
    {
        if ($link === null) {
            $this->page->navbar->add($text);
        } else {
            $this->page->navbar->add($text, new \moodle_url($link));
        }
    }

    /**
     * Set context for the current page.
     *
     * @param  \stdClass  $context
     *
     * @return void
     */
    public function setContext($context)
    {
        $this->page->set_context($context);
    }

    /**
     * Sets the URL for the page. Required by some Moodle pages.
     *
     * @param  string  $url
     * @param  array  $args
     *
     * @return void
     */
    public function setUrl($url, $args = [])
    {
        $this->page->set_url($url, $args);
    }

    /**
     * Set the title for the current page.
     *
     * @param  string  $title
     */
    public function setTitle($title)
    {
        $this->page->set_title($title);
    }

    public function setContextToModule($courseModuleId)
    {
        $this->page->set_context(\context_module::instance($courseModuleId));
    }
}
