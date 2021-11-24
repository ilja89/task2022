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
}
