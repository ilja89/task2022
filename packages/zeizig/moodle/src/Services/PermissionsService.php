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
     * @param  Application  $app
     * @param  User  $user
     * @param  Page  $page
     */
    public function __construct(Application $app, User $user, Page $page)
    {
        parent::__construct($app);
        $this->user = $user;
        $this->page = $page;
    }


    /**
     * Requires login.
     *
     * @return void
     */
    public function requireLogin()
    {
        if (config('app.env') === 'testing') {
            // TODO: Refactor this somehow. Ideally move test checks away from code.
            // This is used when testing views.
            return;
        }
        require_login();
    }

    /**
     * Requires login and enrollment to the given course.
     * Returns true if permission granted.
     *
     * @param  integer  $courseId
     *
     * @return void
     */
    public function requireEnrollmentToCourse($courseId)
    {
        if (config('app.env') === 'testing') {
            // TODO: Refactor this somehow. Ideally move test checks away from code.
            // This is used when testing views.
            return;
        }

        require_login($courseId);
    }

    /**
     * Redirects to the enrol page for given course.
     *
     * @param  integer  $courseId
     *
     * @return void
     */
    public function redirectToEnrol($courseId)
    {
        global $CFG;
        redirect($CFG->wwwroot . '/enrol/index.php?id=' . $courseId);
    }

    /**
     * Requires the capability for the user to view grades for given course.
     *
     * @param  integer  $courseId
     *
     * @return void
     */
    public function requireViewGradesCapability($courseId)
    {
        global $DB;
        $course  = $DB->get_record('course', ['id' => $courseId]);
        $context = \context_course::instance($course->id);
        require_capability('gradereport/grader:view', $context);
        require_capability('moodle/grade:viewall', $context);
    }

    /**
     * Require the capability for the current user to manage the given course.
     *
     * @param  integer  $courseId
     *
     * @return void
     */
    public function requireCourseManagementCapability($courseId)
    {
        $context = \context_course::instance($courseId);
        $this->page->setContext($context);

        require_capability('moodle/course:manageactivities', $context);
    }
}
