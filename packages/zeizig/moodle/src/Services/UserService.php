<?php

namespace Zeizig\Moodle\Services;

use Illuminate\Support\Facades\Log;
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
     * Finds a user by its id.
     * @param int $id
     * @return mixed
     */
    public function findUserById(int $id)
    {
        return User::where('id', $id)->first();
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
        $users = User::where('username', $name . '@ttu.ee')->get();
        if (count($users) == 0) {
            Log::info("@ttu.ee user was not found, continue searching with @taltech.ee. Current username: " . $name);
            $users2 = User::where('username', $name . '@taltech.ee')->get();
            if (count($users2) == 0) {
                Log::info("School username was not found (uniid@taltech.ee), continue searching with username. Current username: " . $name);
                return User::where('username', $name)->first();
            }
            return $users2[0];
        }
        return $users[0];
    }

    /**
     * Returns uniid if the user has school username (which ends in '@ttu.ee' or '@taltech.ee')
     * or if the user does not have school username returns the username as it is
     *
     * @param string $username
     * @return string
     */
    public function getUniidIfTaltechUsername(string $username): string
    {
        if (str_ends_with($username, '@ttu.ee') or str_ends_with($username, '@taltech.ee')) {
            return strtok($username, "@");
        }
        return $username;
    }
}
