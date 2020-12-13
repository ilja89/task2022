<?php

namespace TTU\Charon\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;

class CharonDefenseLabRepository
{
    public function deleteDefenseLabByLabAndCharon($labId, $charonId)
    {
        Log::info("Attempting to delete defense lab by lab_id" . $labId . " and charon_id " . $charonId);
        return DB::table('charon_defense_lab')
            ->where('charon_id', $charonId)
            ->where('lab_id', $labId)
            ->delete();

    }

    public function getDefenseLabsByCharonId($charonId)
    {
        return DB::table('charon_defense_lab')
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

    /**
     * @param $defenseLabId
     *
     * @return Lab
     * @throws ModelNotFoundException
     */
    public function getLabByDefenseLabId($defenseLabId)
    {
        return CharonDefenseLab::findOrFail($defenseLabId)->lab;
    }
}
