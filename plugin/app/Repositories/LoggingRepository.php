<?php

namespace TTU\Charon\Repositories;

use TTU\Charon\Models\QueryLogUsers;
use Zeizig\Moodle\Models\User;

class LoggingRepository
{
    /**
     * @param int $userId
     *
     * @return int
     */
    public function findUserWithQueryLoggingEnabled(int $userId)
    {
        $user = QueryLogUsers::where('user_id', $userId)
            ->first();
        return $user ? 1 : 0;
    }

    public function addUserToLogging(int $userId)
    {
        $queryLogUser = new QueryLogUsers;

        $queryLogUser->user_id = $userId;

        $queryLogUser->save();
    }

    public function removeUserFromLogging(int $userId)
    {
        $recordToDelete = QueryLogUsers::where('user_id', $userId);

        $recordToDelete->delete();
    }
}