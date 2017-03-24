<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Repositories\StudentsRepository;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;

/**
 * Class StudentsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class StudentsController extends Controller
{
    /** @var StudentsRepository */
    protected $studentsRepository;

    /**
     * StudentsController constructor.
     *
     * @param  Request  $request
     * @param  StudentsRepository  $studentsRepository
     */
    public function __construct(Request $request, StudentsRepository $studentsRepository)
    {
        parent::__construct($request);
        $this->studentsRepository = $studentsRepository;
    }

    /**
     * Search students by the given keyword.
     *
     * @param  Course  $course
     *
     * @return Collection
     */
    public function searchStudents(Course $course)
    {
        $keyword = $this->request['q'];

        $users = $this->studentsRepository->searchStudentsByCourseAndKeyword($course->id, $keyword);

        return $users;
    }

    /**
     * Find the user by the given ID.
     *
     * @param Course $course
     * @param  int $userId
     *
     * @return User
     */
    public function findById(Course $course, $userId)
    {
        return User::where('id', $userId)
            ->first(['id', 'firstname', 'lastname']);
    }
}
