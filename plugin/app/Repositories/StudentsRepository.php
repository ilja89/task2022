<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Class StudentsRepository.
 *
 * @package TTU\Charon\Repositories
 */
class StudentsRepository
{
    /**
     * @param  integer  $courseId
     * @param  string  $keyword
     */
    public function searchStudentsByCourseAndKeyword($courseId, $keyword)
    {

        $keyword = '%' . strtolower($keyword) . '%';

        $users = DB::table('role_assignments')
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
            ->select('user.id', 'idnumber', 'firstname', 'lastname', DB::raw("CONCAT(firstname, ' ',lastname, ' (',username, ')') AS fullname"))
            ->get();

        return $users;
    }
}
