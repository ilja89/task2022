<?php

namespace TTU\Charon\Repositories;

use TTU\Charon\Models\QueryLogUsers;

class LoggingRepository
{
    /**
     * @param int $userId
     *
     * @return int
     */
    public function findUserWithQueryLoggingEnabled(int $userId): int
    {
        return QueryLogUsers::where('user_id', '=', $userId)->exists();
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