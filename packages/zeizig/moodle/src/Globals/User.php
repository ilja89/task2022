<?php

namespace Zeizig\Moodle\Globals;

use Zeizig\Moodle\Models\User as UserModel;

/**
 * Class User.
 * Wrapper for the Moodle $USER global.
 *
 * @package Zeizig\Moodle\Globals
 */
class User
{
    /** @var \stdClass */
    protected $user;

    /**
     * User constructor.
     */
    public function __construct()
    {
        global $USER;
        $this->user = $USER;
    }

    /**
     * Get the currently logged in user.
     *
     * @return UserModel
     */
    public function currentUser()
    {
        $id = $this->currentUserId();
        return UserModel::where('id', $id)
            ->first();
    }

    /**
     * Get the currently logged in user id.
     *
     * @return integer
     */
    public function currentUserId()
    {
        return $this->user->id;
    }

    /**
     * Check if the current user is enrolled to the given course.
     *
     * @param  integer  $courseId
     *
     * @return boolean
     */
    public function isEnrolled($courseId)
    {
        if (isset($this->user->enrol['enrolled'][$courseId])) {
            if ($this->user->enrol['enrolled'][$courseId] > time()) {
                return true;
            }
        }
        if (isset($this->user->enrol['tempguest'][$courseId])) {
            if ($this->user->enrol['tempguest'][$courseId] == 0) {
                return true;
            }
        }

        return false;
    }
}
