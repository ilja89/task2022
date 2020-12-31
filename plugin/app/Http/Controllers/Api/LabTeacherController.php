<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\ModelUser;
use TTU\Charon\Repositories\LabTeacherRepository;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Models\Course;

/**
 * Class CommentsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class LabTeacherController extends Controller
{
    /** @var LabTeacherRepository */
    private $labTeacherRepository;

    /** @var User */
    private $user;

    /**
     * CommentsController constructor.
     *
     * @param Request $request
     * @param LabTeacherRepository $labTeacherRepository
     * @param User $user
     */
    public function __construct(Request $request, LabTeacherRepository $labTeacherRepository, User $user)
    {
        parent::__construct($request);
        $this->labTeacherRepository = $labTeacherRepository;
        $this->user = $user;
    }

    public function getByLab(Course $course, Lab $lab)
    {
        return $this->labTeacherRepository->getTeachersByLabId($course->id, $lab->id);
    }

    /**
     * Get teachers by charon and defense lab id.
     *
     * @param Charon $charon
     * @param CharonDefenseLab $charonDefenseLab
     *
     * @return array|Collection
     */
    public function getByCharonAndLab(Charon $charon, CharonDefenseLab $charonDefenseLab)
    {
        return $this->labTeacherRepository->getTeachersByCharonAndDefenseLabId($charon->id, $charonDefenseLab->id);
    }

    /**
     * Get teachers by course.
     *
     * @param Course $course
     *
     * @return \Zeizig\Moodle\Models\User[]
     **/
    public function getTeachersByCourse(Course $course)
    {
        return $this->labTeacherRepository->getTeachersByCourseId($course->id);
    }

    public function getTeacherReportByCourse(Course $course)
    {
        return $this->labTeacherRepository->getTeacherReportByCourseId($course->id);
    }

    public function getTeacherForStudent(Course $course, ModelUser $user)
    {
        return $this->labTeacherRepository->getTeacherForStudent($user->id, $course->id);
    }

    public function updateTeacherForLab($course, $lab, $teacher)
    {
        return $this->labTeacherRepository->updateTeacher($lab, $teacher, $this->request);
    }

    public function getByTeacher($courseId, $teacherId)
    {
        return $this->labTeacherRepository->getTeacherSpecifics($courseId, $teacherId);
    }

    public function getTeacherAggregatedData($courseId, $teacherId)
    {
        return $this->labTeacherRepository->getTeacherAggregatedData($courseId, $teacherId);
    }
}
