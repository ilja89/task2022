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
    
    /* Function needed to get list of students related to exact course
     * 
     * @param int $courseId
     * 
     * @return array $nameList
     */
     
    public function getNamesOfStudentsRelatedToCourse(int $courseId)
    {
        $nameList = null;
        $studentList=json_decode(json_encode($this->searchStudentsByCourseAndKeyword($courseId,"")),true);
        for($i=0;$i<count($studentList);$i++)
        {
            $nameList[$i] = $studentList[$i]["username"];
        }
        return $nameList;
    }
}
