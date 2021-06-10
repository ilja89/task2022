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
    public function find(int $id): User
    {
        return User::find($id);
    }

    /**
     * @param int $id
     *
     * @return User
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function userGroups(int $id): array
    {
        return User::with('groups')->find($id)->groups->pluck('id')->all();
    }
}
