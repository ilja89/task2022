<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;

class DeadlinesRepository
{
    public function deleteAllDeadlinesForCharon($charonId)
    {
        return DB::table('charon_deadline')
            ->where('charon_id', $charonId)
            ->delete();
    }
}
