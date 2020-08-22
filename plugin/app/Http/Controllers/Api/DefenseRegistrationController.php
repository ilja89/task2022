<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Defenders;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use Zeizig\Moodle\Models\Course;

class DefenseRegistrationController extends Controller
{
    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /**
     * LabDummyController constructor.
     *
     * @param Request $request
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     */
    public function __construct(Request $request, DefenseRegistrationRepository $defenseRegistrationRepository)
    {
        parent::__construct($request);
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
    }

    /**
     * Get defense registrations by course.
     * @param Course $course
     * @return Collection|Defenders[]
     */
    public function getDefenseRegistrationsByCourse(Course $course) {
        return $this->defenseRegistrationRepository->getDefenseRegistrationsByCourse($course->id);
    }

    /**
     * Get defense registrations by course, filtered by before and after date.
     * @param Course $course
     * @param $after
     * @param $before
     * @return Collection|Defenders[]
     */
    public function getDefenseRegistrationsByCourseFiltered(Course $course, $after, $before) {
        return $this->defenseRegistrationRepository->getDefenseRegistrationsByCourseFiltered($course->id, $after, $before);
    }

    /**
     * Save defense progress.
     * @param Course $course
     * @param Defenders $defenders
     * @return Defenders
     */
    public function saveProgress(Course $course, Defenders $defenders) {
        return $this->defenseRegistrationRepository->saveProgress($defenders->id, $this->request['progress']);
    }

}
