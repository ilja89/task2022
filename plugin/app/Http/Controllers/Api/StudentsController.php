<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\StudentsRepository;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class StudentsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class StudentsController extends Controller
{
    /** @var StudentsRepository */
    protected $studentsRepository;

    /** @var GradebookService */
    private $gradebookService;

    /**
     * StudentsController constructor.
     *
     * @param  Request $request
     * @param  StudentsRepository $studentsRepository
     * @param GradebookService $gradebookService
     */
    public function __construct(Request $request, StudentsRepository $studentsRepository, GradebookService $gradebookService)
    {
        parent::__construct($request);
        $this->studentsRepository = $studentsRepository;
        $this->gradebookService = $gradebookService;
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

    /**
     * Get active result for the given charon and user. Takes the value
     * from gradebook.
     *
     * @param  Charon  $charon
     * @param  User  $user
     *
     * @return float
     */
    public function getStudentActiveResultForCharon(Charon $charon, User $user)
    {
        $categoryGradeItem = $this->gradebookService->getGradeItemByCategoryId($charon->category_id);
        $categoryGradeGrade = $this->gradebookService->getGradeForGradeItemAndUser($categoryGradeItem->id, $user->id);

        if ($categoryGradeGrade !== null) {
            return $categoryGradeGrade->finalgrade;
        } else {
            return 0;
        }
    }
}
