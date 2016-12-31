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
}
