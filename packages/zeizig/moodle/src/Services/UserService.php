<?php

namespace Zeizig\Moodle\Services;

use Zeizig\Moodle\Models\User;

/**
 * Class UserService.
 * Used to do User specific stuff like searching.
 *
 * @package Zeizig\Moodle\Services
 */
class UserService
{
    /**
     * Finds a user by its id number. The id number should be unique.
     *
     * @param  string  $idNumber
     *
     * @return User
     */
    public function findUserByIdNumber($idNumber)
    {
        return User::where('idnumber', $idNumber . config('moodle.user_id_number_postfix'))->first();
    }

    /**
     * Finds a user by its email. The email should be unique.
     *
     * @param  string  $email
     *
     * @return User
     **/
    public function findUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    /**
     * Finds a user by its uniid. The uniid should be unique.
     *
     * @param  string  $name
     *
     * @return User
     **/
    public function findUserByUniid($name)
    {
        return User::where('username', 'like', $name . '%')->first();
    }
}
