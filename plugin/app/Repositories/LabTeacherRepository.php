<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\LabTeacher;

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

    public function getTeachersByLabAndCourse($courseId, $labId)
    {
        return DB::table('charon_lab_teacher')
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

    /**
     * @param $labId
     *
     * @return int
     */
    public function countLabTeachers($labId): int
    {
        return LabTeacher::where('lab_id', $labId)->count();
    }

    /**
     * @param $charonId
     * @param $labId
     *
     * @return Collection
     */
    public function getTeachersByCharonAndLab($charonId, $labId)
    {
        return DB::table('charon_lab_teacher')
            ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab_teacher.lab_id')
            ->where('charon_defense_lab.charon_id', $charonId)
            ->where('charon_defense_lab.lab_id', $labId)
            ->join('user', 'user.id', 'charon_lab_teacher.teacher_id')
            ->select(
                'user.id',
                'user.firstname',
                'user.lastname',
                'charon_lab_teacher.teacher_location',
                DB::raw("CONCAT(firstname, ' ', lastname) AS fullname")
            )->get();
    }

    /**
     * @param array $teacherIds
     * @param Carbon $time
     *
     * @return array
     */
    public function checkWhichTeachersBusyAt(array $teacherIds, Carbon $time): array
    {
        return DB::table('charon_defenders')
            ->join('charon', 'charon.id', 'charon_defenders.charon_id')
            ->select('charon_defenders.teacher_id')
            ->whereDate('charon_defenders.choosen_time', '=', $time->format('Y-m-d'))
            ->whereTime('charon_defenders.choosen_time', '<=', $time->toTimeString())
            ->whereTime(DB::raw('choosen_time + INTERVAL defense_duration MINUTE'), '>', $time->toTimeString())
            ->whereIn('charon_defenders.teacher_id', $teacherIds)
            ->pluck('teacher_id')
            ->all();
    }

    public function getTeachersByCourseId($courseId)
    {
        return DB::table('course')
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

        return DB::table('course')
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
                DB::raw('sum(' . $prefix . 'charon_submission.confirmed) as total_defences')
            )->groupBy('id', 'firstname', 'lastname')
            ->get();
    }

    public function getTeacherSummaryByCourseId($courseId)
    {
        return DB::table('charon_submission')
            ->join('charon', 'charon.id', 'charon_submission.charon_id')
            ->join('user as u_s', 'u_s.id', '=', 'charon_submission.user_id')
            ->join('user as u_t', 'u_t.id', '=', 'charon_submission.grader_id')
            ->where('charon.course', $courseId)
            ->whereNotNull('charon_submission.grader_id')
            ->select(
                'charon.id',
                'charon.name',
                'u_s.username as student',
                'u_t.username as teacher',
                'charon_submission.updated_at'
            )
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
        return array_values(DB::table('role_capabilities')
            ->where('capability', 'moodle/course:manageactivities')
            ->select('roleid')
            ->pluck('roleid')
            ->toArray());
    }

    public function getGroupsForStudent($studentId, $courseId)
    {
        return array_values(DB::table('groupings')
            ->where('groupings.idnumber', 'help_group')
            ->where('groupings.courseid', $courseId)
            ->join('groupings_groups', 'groupings.id', 'groupings_groups.groupingid')
            ->join('groups', 'groupings_groups.groupid', 'groups.id')
            ->join('groups_members', 'groupings_groups.groupid', 'groups_members.groupid')
            ->where('groups_members.userid', $studentId)
            ->pluck('groups.id')
            ->toArray());
    }

    public function getTeacherForStudent($studentId, $courseId)
    {
        return DB::table('groups')
            ->whereIn('groups.id', $this->getGroupsForStudent($studentId, $courseId))
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
        return DB::table('user')
            ->where('id', $userId)
            ->select('id', 'firstname', 'lastname', DB::raw("CONCAT(firstname, ' ', lastname) AS fullname"))
            ->get();
    }

    public function updateTeacher($lab, $teacher, $update)
    {
        return DB::table('charon_lab_teacher')
            ->where('teacher_id', $teacher)
            ->where('lab_id', $lab)
            ->update(['teacher_location' => $update->teacher_location, 'teacher_comment' => $update->teacher_comment]);
    }

    public function getTeacherSpecifics($courseId, $teacherId)
    {
        return DB::table('charon_lab_teacher')
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
        return DB::table('charon_lab_teacher')
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
