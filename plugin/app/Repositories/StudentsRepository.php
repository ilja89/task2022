<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\GradeGrade;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Models\User;

/**
 * Class StudentsRepository.
 *
 * @package TTU\Charon\Repositories
 */
class StudentsRepository
{
    /**
     * @param integer $courseId
     * @param string $keyword
     */
    public function searchStudentsByCourseAndKeyword($courseId, $keyword)
    {
        $keyword = '%' . strtolower($keyword) . '%';

        return DB::table('role_assignments')
            ->join('user', 'role_assignments.userid', '=', 'user.id')
            ->join('context', 'role_assignments.contextid', '=', 'context.id')
            ->where('context.contextlevel', CONTEXT_COURSE)
            ->where('context.instanceid', $courseId)
            ->where(function ($query) use ($keyword) {
                $query->whereRaw('LOWER(idnumber) like ?', [$keyword])
                    ->orWhereRaw('LOWER(username) like ?', [$keyword])
                    ->orWhereRaw('LOWER(firstname) like ?', [$keyword])
                    ->orWhereRaw('LOWER(lastname) like ?', [$keyword])
                    ->orWhereRaw("CONCAT(LOWER(firstname), ' ', LOWER(lastname)) like ?", [$keyword]);
            })
            ->select('user.id', 'idnumber', 'username', 'firstname', 'lastname', DB::raw("CONCAT(firstname, ' ',lastname, ' (',username, ')') AS fullname"))
            ->get();
    }

    /**
     * Find the user by the given ID.
     *
     * @param int $userId
     *
     * @return User
     */
    public function findById($userId)
    {
        return User::where('id', $userId)
            ->first(['id', 'firstname', 'lastname', 'idnumber', 'username']);
    }

    public function getStudentGroups(Course $course, int $userId)
    {
        return $this->findById($userId)->groups()->with('members:idnumber,firstname,lastname,username')->where('courseid', $course->id)->get();
    }

    public function getStudentTotalGrade(Course $course, int $userId)
    {
        $gradeItem = GradeItem::where(array('courseid' => $course->id, 'itemtype' => 'course'))->first();
        $grade = GradeGrade::where(array('itemid' => $gradeItem->id, 'userid' => $userId))->first();
        if (isset($grade->finalgrade)) {
            return floatval($grade->finalgrade);
        } else {
            return 0;
        }
    }

    public function getUserCharonsDetails($courseId, $userId)
    {
        return DB::select("
        SELECT
            c.id AS charonId,
            c.name AS charonName,
            c.defense_threshold as defThreshold,
            (
                SELECT gg.finalgrade
                FROM mdl_grade_grades gg
                JOIN mdl_grade_items gi
                ON gi.id = gg.itemid
                WHERE gg.userid = ?
                AND gi.itemnumber < 100
                AND gi.iteminstance = charonId
            ) AS studentPoints,
            (
                SELECT gi.grademax
                FROM mdl_grade_items gi
                INNER JOIN mdl_charon c
                ON c.category_id = gi.categoryid
                WHERE gi.itemnumber = 1
                AND gi.iteminstance = charonId
            ) AS maxPoints,
            ' ' AS points,
            (
            SELECT COUNT(confirmed)
            FROM mdl_charon_submission cs
            WHERE cs.user_id = ?
            AND cs.charon_id = charonId
            AND cs.confirmed = 1
            ) AS defended
        FROM mdl_charon c
        WHERE c.course = ?
        ", [$userId, $userId, $courseId]);
    }
}
