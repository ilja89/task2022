<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LabTeacherRepository
{
    public function deleteAllLabTeachersForCharon($charonId) {
        Log::info("Attempting to delete all charon lab-teachers");
        return DB::table('charon_lab_teacher')
            ->where('charon_id', $charonId)
            ->delete();
    }

    public function getTeachersByLabId($courseId, $labId) {
        $labTeachers =  \DB::table('charon_lab_teacher')
            ->join('charon_lab', 'charon_lab.id', 'charon_lab_teacher.lab_id')
            ->where('lab_id', $labId)
            ->where('course_id', $courseId)
            ->join('user', 'user.id', 'teacher_id')
            ->select(
                'user.id',
                'firstName',
                'lastName'
            )
            ->get();

        return $labTeachers;
    }

    public function getTeachersByCharonAndLabId($charonId, $charonDefenseLabId) {
        $teachers = \DB::table('charon_lab_teacher')  // id, lab_id, teacher_id
        ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab_teacher.lab_id') // id, lab_id, charon_id
        ->where('charon_defense_lab.charon_id', $charonId)
            ->where('charon_defense_lab.id', $charonDefenseLabId)
            ->join('user', 'user.id', 'charon_lab_teacher.teacher_id')
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

    public function deleteByLabId($labId) {
        return DB::table('charon_lab_teacher')
            ->where('lab_id', $labId)
            ->delete();
    }

    public function getTeacherForStudent($studentId) {
        $group = \DB::table('user')
            ->join('groups_members', 'groups_members.userid', 'user.id')
            ->where('user.id', $studentId)
            ->select('groups_members.groupid')
            ->get();
        $okRoleIds = \DB::table('role_capabilities')
            ->where('capability', 'moodle/course:manageactivities')
            ->select('roleid')
            ->get();
        $okRoleIdsString = "(";
        if (count($okRoleIds) == 0) {
            $okRoleIdsString = "()";
        }
        for ($i = 0; $i < count($okRoleIds); $i++) {
            $okRoleIdsString .= $okRoleIds[$i]->roleid;
            if ($i != count($okRoleIds) - 1) {
                $okRoleIdsString .= ', ';
            } else {
                $okRoleIdsString .= ')';
            }
        }
        $teacher = \DB::table('groups_members')
            ->join('role_assignments', 'role_assignments.userid', 'groups_members.userid')
            ->join('user', 'user.id', 'groups_members.userid')
            ->where('groups_members.groupid', $group[0]->groupid)
            ->whereRaw("roleid IN " . $okRoleIdsString)
            ->select('user.id', 'user.firstname', 'user.lastname', 'groups_members.groupid')
            ->get();
        return $teacher;
    }
}
