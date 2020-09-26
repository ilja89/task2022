<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;

class LabTeacherRepository
{
    public function deleteAllLabTeachersForCharon($charonId)
    {
        return DB::table('charon_lab_teacher')
            ->where('charon_id', $charonId)
            ->delete();
    }

    public function getTeachersByLabId($courseId, $labId)
    {
        return \DB::table('charon_lab_teacher')
            ->join('charon_lab', 'charon_lab.id', 'charon_lab_teacher.lab_id')
            ->where('lab_id', $labId)
            ->where('course_id', $courseId)
            ->join('user', 'user.id', 'teacher_id')
            ->select(
                'user.id',
                'firstName',
                'lastName',
                'charon_lab_teacher.teacher_location'
            )
            ->get();
    }

    public function getTeachersByCharonAndLabId($charonId, $charonDefenseLabId)
    {
        return \DB::table('charon_lab_teacher')  // id, lab_id, teacher_id
        ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab_teacher.lab_id') // id, lab_id, charon_id
        ->where('charon_defense_lab.charon_id', $charonId)
            ->where('charon_defense_lab.id', $charonDefenseLabId)
            ->join('user', 'user.id', 'charon_lab_teacher.teacher_id')
            ->select('user.id', 'user.firstName', 'user.lastName', 'charon_lab_teacher.teacher_location')
            ->get();
    }

    public function getTeachersByCourseId($courseId)
    {
        return \DB::table('course')
            ->join('context', 'context.instanceid', 'course.id')
            ->join('role_assignments', 'role_assignments.contextid', 'context.id')
            ->join('user', 'user.id', 'role_assignments.userid')
            ->join('role', 'role.id', 'role_assignments.roleid')
            ->where('role.id', 3)
            ->where('course.id', $courseId)
            ->select('user.id', 'user.firstname', 'user.lastname')
            ->get();
    }

    public function getTeacherReportByCourseId($courseId)
    {
        global $CFG;
        $prefix = $CFG->prefix;

        return \DB::table('course')
            ->join('context', 'context.instanceid', 'course.id')
            ->join('role_assignments', 'role_assignments.contextid', 'context.id')
            ->join('user', 'user.id', 'role_assignments.userid')
            ->join('role', 'role.id', 'role_assignments.roleid')
            ->where('role.id', 3)
            ->where('course.id', $courseId)
            ->leftJoin('charon_submission', 'user.id', 'charon_submission.grader_id')
            ->select('user.id as id', 'user.firstname', 'user.lastname', \DB::raw('sum(' . $prefix . 'charon_submission.confirmed) as total_defences'))
            ->groupBy('id', 'firstname', 'lastname')
            ->get();
    }

    public function deleteByLabId($labId)
    {
        return DB::table('charon_lab_teacher')
            ->where('lab_id', $labId)
            ->delete();
    }

    public function deleteByLabAndTeacherId($labId, $teacherId) {
        return DB::table('charon_lab_teacher')
            ->where('lab_id', $labId)
            ->where('teacher_id', $teacherId)
            ->delete();
    }

    public function getTeacherForStudent($studentId)
    {
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

    /**
     * @param $userId
     * @return mixed
     */
    public function getTeacherByUserId($userId)
    {
        return \DB::table('user')
            ->where('id', $userId)
            ->select('id', 'firstname', 'lastname')
            ->get();
    }

    public function updateTeacher($lab, $teacher, $update)
    {
        return \DB::table('charon_lab_teacher')
            ->where('teacher_id', $teacher)
            ->where('lab_id', $lab)
            ->update(['teacher_location' => $update->teacher_location, 'teacher_comment' => $update->teacher_comment]);
    }

    public function getTeacherSpecifics($courseId, $teacherId)
    {
        return \DB::table('charon_lab_teacher')
            ->join('charon_lab', 'charon_lab.id', 'charon_lab_teacher.lab_id')
            ->where('teacher_id', $teacherId)
            ->where('course_id', $courseId)
            ->join('user', 'user.id', 'teacher_id')
            ->select(
                'charon_lab.id',
                'firstName',
                'lastName',
                'teacher_id',
                'course_id',
                'charon_lab.start',
                'charon_lab.end',
                'charon_lab.id as lab_id',
                'charon_lab_teacher.teacher_location',
                'charon_lab_teacher.teacher_comment'
            )
            ->get();
    }

    public function getTeacherAggregatedData($courseId, $teacherId)
    {
        // TODO
        return \DB::table('charon_lab_teacher')
            ->join('charon_lab', 'charon_lab.id', 'charon_lab_teacher.lab_id')
            ->where('teacher_id', $teacherId)
            ->where('course_id', $courseId)
            ->join('user', 'user.id', 'teacher_id')
            ->select(
                'charon_lab.id',
                'firstName',
                'lastName',
                'teacher_id',
                'course_id',
                'charon_lab.start',
                'charon_lab.end',
                'charon_lab.id as lab_id',
                'charon_lab_teacher.teacher_location',
                'charon_lab_teacher.teacher_comment'
            )
            ->get();
    }
}
