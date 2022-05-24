<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Collection;
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
     */
    public function getStudentsCount($courseId)
    {
        return DB::table('role_assignments')
                    ->join('user', 'role_assignments.userid', '=', 'user.id')
                    ->join('context', 'role_assignments.contextid', '=', 'context.id')
                    ->where('context.contextlevel', CONTEXT_COURSE)
                    ->where('context.instanceid', $courseId)
                    ->where('role_assignments.roleid', 5)
                    ->count();
    }

    /**
     * Search users by given course id.
     *
     * @param integer $courseId
     * @param ?string $keyword
     * @param string[] $roleNames
     *
     * @return Collection
     */
    public function searchUsersByCourseKeywordAndRole(
        int $courseId,
        ?string $keyword = null,
        array $roleNames = []
    ): Collection {

        $keyword = '%' . strtolower($keyword) . '%';

        $query = DB::table('role_assignments')
            ->join('role', 'role_assignments.roleid', '=', 'role.id')
            ->join('user', 'role_assignments.userid', '=', 'user.id')
            ->join('context', 'role_assignments.contextid', '=', 'context.id')
            ->where('context.contextlevel', CONTEXT_COURSE)
            ->where('context.instanceid', $courseId);

        if (!empty($roleNames)) {
            $query->whereIn('role.shortname', $roleNames);
        }

        if ($keyword !== null) {
            $query->where(function ($queryRaw) use ($keyword) {
                $queryRaw->whereRaw('LOWER(idnumber) like ?', [$keyword])
                    ->orWhereRaw('LOWER(username) like ?', [$keyword])
                    ->orWhereRaw('LOWER(firstname) like ?', [$keyword])
                    ->orWhereRaw('LOWER(lastname) like ?', [$keyword])
                    ->orWhereRaw("CONCAT(LOWER(firstname), ' ', LOWER(lastname)) like ?", [$keyword]);
            });
        }

        return $query->select(
            'user.id',
            'idnumber',
            'username',
            'firstname',
            'lastname',
            DB::raw("CONCAT(firstname, ' ', lastname, ' (', username, ')') AS fullname")
        )->get();
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

    public function getAllEnrolled(int $courseId)
    {
        $context = \context_course::instance($courseId);
        $users = get_enrolled_users($context);
        return array_map(function ($user) {
            $updatedUser = new \stdClass();
            $updatedUser->id = $user->id;
            $updatedUser->username = $user->username;
            $updatedUser->firstname = $user->firstname;
            $updatedUser->lastname = $user->lastname;

            return $updatedUser;
        }, $users);
    }

    public function getUserCharonsDetails($courseId, $userId)
    {
        $charonDetails = [];
        $charons = DB::table('charon')
            ->where('course', $courseId)
            ->get();

        foreach ($charons as $charon) {
            $studentPoints = DB::table('grade_grades as gg')
                ->join('grade_items as gi', 'gg.itemid', '=', 'gi.id')
                ->where('gi.itemtype', 'category')
                ->where('gi.iteminstance', $charon->category_id)
                ->where('gg.userid', $userId)
                ->value('gg.finalgrade');

            $maxPoints = DB::table('grade_items')
                ->where('itemtype', 'category')
                ->where('iteminstance', $charon->category_id)
                ->value('grademax');

            $defended = DB::table('charon_submission')
                ->where('user_id', $userId)
                ->where('charon_id', $charon->id)
                ->where('confirmed', '1')
                ->count('confirmed');

            $detailObject = new \stdClass();
            $detailObject->charonId = $charon->id;
            $detailObject->charonName = $charon->name;
            $detailObject->defThreshold = $charon->defense_threshold;
            $detailObject->studentPoints = $studentPoints;
            $detailObject->maxPoints = $maxPoints;
            $detailObject->points = ' ';
            $detailObject->defended = $defended;
            array_push($charonDetails, $detailObject);
        }

        return $charonDetails;
    }

    /**
     * @param integer $courseId
     */
    public function getAllByCourse($courseId)
    {
        return DB::table('role_assignments')
            ->join('user', 'role_assignments.userid', '=', 'user.id')
            ->join('context', 'role_assignments.contextid', '=', 'context.id')
            ->where('context.contextlevel', CONTEXT_COURSE)
            ->where('context.instanceid', $courseId)
            ->where('role_assignments.roleid', 5)
            ->select('user.id', DB::raw("CONCAT(firstname, ' ', lastname) AS fullname"), 'user.username')
            ->get();
    }
}
