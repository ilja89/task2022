<?php

namespace TTU\Charon\Repositories;

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
}
