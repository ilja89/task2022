<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;

class CharonDefenseLabRepository
{
    public function deleteDefenseLabByLabAndCharon($lab_id, $charonId)
    {
        Log::info("Attempting to delete defense lab by lab_id" . $lab_id . " and charon_id " . $charonId);
        return DB::table('charon_defense_lab')
            ->where('charon_id', $charonId)
            ->where('lab_id', $lab_id)
            ->delete();

    }

    public function getDefenseLabsByCharonId($charonId)
    {
        return \DB::table('charon_defense_lab')
            ->where('charon_id', $charonId)
            ->join('charon_lab', 'charon_lab.id', 'charon_defense_lab.lab_id')
            ->select(
                'charon_lab.id',
                'charon_lab.start',
                'charon_lab.end',
                'charon_lab.course_id'
            )
            ->get();
    }

    public function getDefenseLabById($labId)
    {
        return \DB::table('charon_defense_lab')
            ->where('charon_defense_lab.id', $labId)
            ->join('charon_lab', 'charon_lab.id', 'charon_defense_lab.lab_id')
            ->select(
                'charon_lab.id',
                'charon_lab.start',
                'charon_lab.end',
                'charon_lab.course_id'
            )
            ->first();
    }

}
