<?php

namespace Zeizig\Moodle\Services;

use Illuminate\Contracts\Foundation\Application;
use Zeizig\Moodle\Globals\Page;
use Zeizig\Moodle\Globals\User;

/**
 * Class Permissions.
 * Wrapper for Moodle permissions functions.
 *
 * @package Zeizig\Moodle\Services
 */
class PermissionsService extends MoodleService
{
    /** @var User */
    protected $user;
    /** @var Page */
    protected $page;

    /**
     * PermissionsService constructor.
     *
     * @param Application $app
     * @param User $user
     * @param Page $page
     */
    public function __construct(Application $app, User $user, Page $page)
    {
        parent::__construct($app);
        $this->user = $user;
        $this->page = $page;
    }

    /**
     * Requires login and enrollment to the given course.
     *
     * @param integer $courseId
     *
     * @return void
     */
    public function requireEnrollmentToCourse($courseId)
    {
        require_login($courseId);
    }

    /**
     * Require the capability for the current user to manage the given course.
     *
     * @param integer $courseId
     *
     * @return void
     */
    public function requireCourseManagementCapability($courseId, $throwException = false)
    {
        $context = \context_course::instance($courseId);
        $this->page->setContext($context);

        require_capability('moodle/course:manageactivities', $context);
    }

    public function canManageCourse($courseId)
    {
        if (config('app.env') === 'testing') {
            // TODO: Make dynamic, tests for student and teacher.
            return false;
        }

        $context = \context_course::instance($courseId);
        return has_capability('moodle/course:manageactivities', $context);
    }
}
