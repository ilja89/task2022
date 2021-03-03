<?php

namespace Zeizig\Moodle\Globals;

/**
 * Class Course.
 * Wrapper for the $COURSE global.
 *
 * @package Zeizig\Moodle\Globals
 */
class Course
{
    /** @var \stdClass */
    protected $course;

    /**
     * Course constructor.
     */
    public function __construct()
    {
        global $COURSE;
        $this->course = $COURSE;
    }

    /**
     * Get the current course short name.
     *
     * @return string
     */
    public function getCourseShortName()
    {
        return $this->course->shortname;
    }

    /**
     * Get the current course full name.
     *
     * @return string
     */
    public function getCourseFullName()
    {
        return $this->course->fullname;
    }

    /**
     * Get the current course ID.
     *
     * @return integer
     */
    public function getCourseId()
    {
        return $this->course->id;
    }
}