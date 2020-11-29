<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;
use TTU\Charon\Facades\MoodleConfig;

class LabTeacherRepository
{
    /** @var MoodleConfig */
    private $moodleConfig;

    /**
     * @param MoodleConfig $moodleConfig
     */
    public function __construct(MoodleConfig $moodleConfig)
    {
        $this->moodleConfig = $moodleConfig;
    }

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
                'firstname',
                'lastname',
                'charon_lab_teacher.teacher_location',
                DB::raw("CONCAT(firstname, ' ',lastname) AS fullname")
            )->get();
    }

    public function getTeachersByCharonAndLabId($charonId, $charonDefenseLabId)
    {
        return \DB::table('charon_lab_teacher')  // id, lab_id, teacher_id
        ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab_teacher.lab_id') // id, lab_id, charon_id
        ->where('charon_defense_lab.charon_id', $charonId)
            ->where('charon_defense_lab.id', $charonDefenseLabId)
            ->join('user', 'user.id', 'charon_lab_teacher.teacher_id')
            ->select(
                'user.id',
                'user.firstname',
                'user.lastname',
                'charon_lab_teacher.teacher_location',
                DB::raw("CONCAT(firstname, ' ', lastname) AS fullname")
            )->get();
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
            ->select(
                'user.id',
                'user.firstname',
                'user.lastname',
                DB::raw("CONCAT(firstname, ' ', lastname) AS fullname")
            )->get();
    }

    public function getTeacherReportByCourseId($courseId)
    {
        $prefix = $this->moodleConfig->prefix;

        return \DB::table('course')
            ->join('context', 'context.instanceid', 'course.id')
            ->join('role_assignments', 'role_assignments.contextid', 'context.id')
            ->join('user', 'user.id', 'role_assignments.userid')
            ->join('role', 'role.id', 'role_assignments.roleid')
            ->where('role.id', 3)
            ->where('course.id', $courseId)
            ->leftJoin('charon_submission', 'user.id', 'charon_submission.grader_id')
            ->select(
                'user.id as id',
                'user.firstname',
                'user.lastname',
                DB::raw("CONCAT(firstname, ' ', lastname) AS fullname"),
                \DB::raw('sum(' . $prefix . 'charon_submission.confirmed) as total_defences')
            )->groupBy('id', 'firstname', 'lastname')
            ->get();
    }

    public function deleteByLabId($labId)
    {
        return DB::table('charon_lab_teacher')
            ->where('lab_id', $labId)
            ->delete();
    }

    public function deleteByLabAndTeacherId($labId, $teacherId)
    {
        return DB::table('charon_lab_teacher')
            ->where('lab_id', $labId)
            ->where('teacher_id', $teacherId)
            ->delete();
    }

    public function getTeacherRoleIds()
    {
        return array_values(\DB::table('role_capabilities')
            ->where('capability', 'moodle/course:manageactivities')
            ->select('roleid')
            ->pluck('roleid')
            ->toArray());
    }

    public function getGroupsForStudent($studentId, $course_id)
    {
        return array_values(\DB::table('groupings')
            ->where('groupings.idnumber', 'help_group')
            ->where('groupings.courseid', $course_id)
            ->join('groupings_groups', 'groupings.id', 'groupings_groups.groupingid')
            ->join('groups', 'groupings_groups.groupid', 'groups.id')
            ->join('groups_members', 'groupings_groups.groupid', 'groups_members.groupid')
            ->where('groups_members.userid', $studentId)
            ->pluck('groups.id')
            ->toArray());
    }

    public function getTeacherForStudent($studentId, $course_id)
    {

        return \DB::table('groups')
            ->whereIn('groups.id', $this->getGroupsForStudent($studentId, $course_id))
            ->join('groups_members', 'groups.id', 'groups_members.groupid')
            ->join('user', 'user.id', 'groups_members.userid')
            ->join('role_assignments', 'user.id', 'role_assignments.userid')
            ->whereIn("role_assignments.roleid", $this->getTeacherRoleIds())
            ->select(
                'user.id',
                'user.firstname',
                'user.lastname',
                DB::raw("CONCAT(firstname, ' ', lastname) AS fullname"),
                'groups_members.groupid'
            )->distinct()
            ->first();
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getTeacherByUserId($userId)
    {
        return \DB::table('user')
            ->where('id', $userId)
            ->select('id', 'firstname', 'lastname', DB::raw("CONCAT(firstname, ' ', lastname) AS fullname"))
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
                'firstname',
                'lastname',
                DB::raw("CONCAT(firstname, ' ', lastname) AS fullname"),
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
                'firstname',
                'lastname',
                DB::raw("CONCAT(firstname, ' ', lastname) AS fullname"),
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
