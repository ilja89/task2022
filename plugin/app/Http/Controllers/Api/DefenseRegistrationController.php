<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
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

    public function getDefenseRegistrationsByCourse(Course $course) {
        return $this->defenseRegistrationRepository->getDefenseRegistrationsByCourse($course->id);
    }

    public function getDefenseRegistrationsByCourseFiltered(Course $course, $after, $before) {
        return $this->defenseRegistrationRepository->getDefenseRegistrationsByCourseFiltered($course->id, $after, $before);
    }

}
