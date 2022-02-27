<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\Log;
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

    public function updateUsersWithLogging($users)
    {
        $usersWithLoggingEnabled = QueryLogUsers::all();

        $enabledIds = array();
        foreach ($usersWithLoggingEnabled as $key => $enabledUser) {
            $enabledIds[$key] = $enabledUser->user_id;
        }

        foreach ($users as $user) {
            $userId = $user->id;
            if (in_array($userId, $enabledIds)) {
                $user->logging = true;
            } else {
                $user->logging = false;
            }
        }
        return $users;
    }
}