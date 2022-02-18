<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\Log;
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
        return QueryLogUsers::where('user_id', $userId)
            ->first();
    }
}