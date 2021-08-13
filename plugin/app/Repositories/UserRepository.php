<?php

namespace TTU\Charon\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Zeizig\Moodle\Models\User;

class UserRepository
{
    /**
     * @param int $id
     *
     * @return User
     */
    public function find(int $id)
    {
        return User::find($id);
    }

    /**
     * @param int $id
     *
     * @return User
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id)
    {
        return User::findOrFail($id);
    }

    //Get user username using user id
    public function getUsernameById($userId)
    {
        return \DB::table('user')
            ->where("id", $userId)
            ->select("username")
            ->first()->username;
    }
}
