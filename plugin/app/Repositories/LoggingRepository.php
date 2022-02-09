<?php

namespace TTU\Charon\Repositories;

use TTU\Charon\Models\QueryLogUsers;
use Zeizig\Moodle\Models\User;

class LoggingRepository
{
    /**
     * @param int $userId
     *
     * @return User
     */
    public function findUserWithQueryLoggingEnabled(int $userId)
    {
        QueryLogUsers::find($userId)->user;
    }
}