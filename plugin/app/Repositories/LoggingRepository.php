<?php

namespace TTU\Charon\Repositories;

use TTU\Charon\Models\QueryLogUser;

class LoggingRepository
{
    /**
     * @param int $userId
     *
     * @return int
     */
    public function findUserWithQueryLoggingEnabled(int $userId): int
    {
        return QueryLogUser::where('user_id', '=', $userId)->exists();
    }

    public function addUserToLogging(int $userId)
    {
        $queryLogUser = new QueryLogUser;

        $queryLogUser->user_id = $userId;

        $queryLogUser->save();
    }

    public function removeUserFromLogging(int $userId)
    {
        $recordToDelete = QueryLogUser::where('user_id', $userId);

        $recordToDelete->delete();
    }

    public function findUsersWithLogging(): array
    {
        $usersWithLoggingEnabled = QueryLogUser::all();

        $enabledIds = array();
        foreach ($usersWithLoggingEnabled as $key => $enabledUser) {
            $enabledIds[$key] = $enabledUser->user_id;
        }

        return $enabledIds;
    }
}