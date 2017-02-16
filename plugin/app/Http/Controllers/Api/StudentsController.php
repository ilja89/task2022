<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Repositories\StudentsRepository;
use Zeizig\Moodle\Models\Course;

/**
 * Class StudentsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class StudentsController extends Controller
{
    /** @var Request */
    protected $request;

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
        $this->request = $request;
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
}
