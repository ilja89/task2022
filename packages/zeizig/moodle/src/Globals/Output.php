<?php

namespace Zeizig\Moodle\Globals;

/**
 * Class Output. This is a wrapper for Moodle $OUTPUT global.
 *
 * @package Zeizig\Moodle\Globals
 */
class Output
{
    /** @var \StdClass */
    protected $output;

    /**
     * Output constructor.
     */
    public function __construct()
    {
        global $OUTPUT;
        $this->output = $OUTPUT;
    }

    /**
     * Get the Moodle page header.
     *
     * @return string
     */
    public function header()
    {
        return $this->output->header();
    }

    /**
     * Get the Moodle page footer.
     *
     * @return string
     */
    public function footer()
    {
        return $this->output->footer();
    }
}