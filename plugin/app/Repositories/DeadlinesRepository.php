<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeadlinesRepository
{
    public function deleteAllDeadlinesForCharon($charonId)
    {
        Log::info("Attempting to delete all charon deadlines");
        return DB::table('charon_deadline')
            ->where('charon_id', $charonId)
            ->delete();
    }

    public function deleteAllCalendarEventsForCharon($charonId)
    {
        Log::info("Attempting to delete all charon events");
        return DB::table('event')
            ->where('instance', $charonId)
            ->delete();
    }

    public function addCharonDeadlinesToCalendarEvents($data)
    {
        Log::info("Add deadline event");
        DB::table('event')->insert($data);
    }
}
