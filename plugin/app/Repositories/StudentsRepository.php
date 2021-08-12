<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;
// use TTU\Charon\Models\Grademap;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\GradeGrade;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class StudentsRepository.
 *
 * @package TTU\Charon\Repositories
 */
class StudentsRepository
{

/** @var GradebookService */
    private $gradebookService;

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
        $charons = DB::table('charon')
            ->where('course', $courseId)
            ->select('id', 'name')
            ->get();

        foreach ($charons as $charon) {
            $charon->maxPoints = sprintf('%.2f', DB::table('grade_items')
                ->join('charon', 'charon.category_id', '=', 'grade_items.categoryid')
                ->where('grade_items.itemnumber', '=', 1)
                ->where('grade_items.iteminstance', '=', $charon->id)
                ->value('grade_items.grademax'));

            $charon->studentPoints = sprintf('%.2f', DB::table('grade_grades')
                ->join('grade_items', 'grade_items.id', '=', 'grade_grades.itemid')
                ->where('grade_grades.userid', '=', $userId)
                ->where('grade_items.itemnumber', '<', 100)
                ->where('grade_items.iteminstance', '=', $charon->id)
                ->value('finalgrade'));

            $charon->defended = (DB::table('charon_submission')
                ->where('charon_submission.user_id', '=', $userId)
                ->where('charon_submission.charon_id', '=', $charon->id)
                ->where('charon_submission.confirmed', '=', 1)
                ->count() == 1) ? 'Yes' : 'No';
        }

        return $charons;
    }
}
