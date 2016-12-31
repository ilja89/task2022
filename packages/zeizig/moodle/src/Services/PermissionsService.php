<?php

namespace Zeizig\Moodle\Services;

/**
 * Class Permissions.
 * Wrapper for Moodle permissions functions.
 *
 * @package Zeizig\Moodle\Services
 */
class PermissionsService extends MoodleService
{
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
     * @return boolean
     */
    public function requireEnrollmentToCourse($courseId)
    {
        if (config('app.env') === 'testing') {
            // TODO: Refactor this somehow. Ideally move test checks away from code.
            // This is used when testing views.
            return true;
        }

        // TODO: Try to fix require_login.
//        try {
//            // Do not redirect (last parameter, true) so we can catch the exception and call redirect ourselves.
//            require_login($courseId, true, null, true, true);
//        } catch (\require_login_exception $e) {
//            return false;
//        }

        // TODO: Bit of a hack, refactor to use require_login
        if (!is_siteadmin() && !$this->user->isEnrolled($courseId)) {
            return false;
        }

        return true;
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
}
