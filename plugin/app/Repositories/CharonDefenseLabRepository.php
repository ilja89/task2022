<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;

class CharonDefenseLabRepository
{
    public function deleteAllDefenseLabsForCharon($charonId)
    {

        Log::info("Attempting to delete all charon defense-labs");
        return DB::table('charon_defense_lab')
            ->where('charon_id', $charonId)
            ->delete();

    }

    public function getDefenseLabsByCharonId($charonId) {

        $defenseLabs =  \DB::table('charon_defense_lab')
            ->where('charon_id', $charonId)
            ->join('lab', 'lab.id', 'charon_defense_lab.lab_id')
            ->select(
                'lab.id',
                'lab.start'
            )
            ->get();

        return $defenseLabs;
    }

}
