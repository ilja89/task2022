<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\LabDummy;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\LabDummyRepository;
use Zeizig\Moodle\Models\Course;

class LabDummyController extends Controller
{
    /** @var LabDummyRepository */
    private $labDummyRepository;

    /**
     * LabDummyController constructor.
     *
     * @param Request $request
     * @param LabDummyRepository $labDummyRepository
     */
    public function __construct(Request $request, LabDummyRepository $labDummyRepository)
    {
        parent::__construct($request);
        $this->labDummyRepository = $labDummyRepository;
    }

    /**
     * Get Charons by course.
     *
     * @param  Course $course
     *
     * @return \Illuminate\Database\Eloquent\Collection|LabDummy[]
     */
    public function getByCourse(Course $course)
    {
        return $this->labDummyRepository->findLabDummiesByCourse($course->id);
    }
}
