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

    public function getTeachersByLabId($courseId, $labId) {

        $labTeachers =  \DB::table('lab_teacher')
            ->join('lab', 'lab.id', 'lab_teacher.lab_id')
            ->where('lab_id', $labId)
            ->where('course_id', $courseId)
            ->join('user', 'user.id', 'teacher_id')
            ->select(
                'user.id',
                'firstName',
                'lastName'
            )
            ->get();
        // is the foreach get thing important? Don't know, let's find out

        return $labTeachers;
    }

    public function getTeachersByCharonAndLabId($charonId, $charonDefenseLabId) {
        $teachers = \DB::table('lab_teacher')  // id, lab_id, teacher_id
            ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'lab_teacher.lab_id') // id, lab_id, charon_id
            ->where('charon_defense_lab.charon_id', $charonId)
            ->where('charon_defense_lab.id', $charonDefenseLabId)
            ->join('user', 'user.id', 'lab_teacher.teacher_id')
            ->select('user.id', 'user.firstName', 'user.lastName')
            ->get();
        return $teachers;
    }

    public function getTeachersByCourseId($courseId) {
        $teachers = \DB::table('course')
            ->join('context', 'context.instanceid', 'course.id')
            ->join('role_assignments', 'role_assignments.contextid', 'context.id')
            ->join('user', 'user.id', 'role_assignments.userid')
            ->join('role', 'role.id', 'role_assignments.roleid')
            ->where('role.id', 3)
            ->where('course.id', $courseId)
            ->select('user.id', 'user.firstname', 'user.lastname')
            ->get();
        return $teachers;
    }

}
