<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;

class LabTeacherRepository
{
    public function deleteAllLabTeachersForCharon($charonId)
    {

        Log::info("Attempting to delete all charon lab-teachers");
        return DB::table('lab_teacher')
            ->where('charon_id', $charonId)
            ->delete();

    }

    public function getTeachersByLabId($labId) {

        $labTeachers =  \DB::table('lab_teacher')
            ->where('lab_id', $labId)
            ->select(
                'id',
                'lab_id',
                'teacher_id'
            )
            ->get();
        // is the foreach get thing important? Don't know, let's find out

        return $labTeachers;
    }

}
