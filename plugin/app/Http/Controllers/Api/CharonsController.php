<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use Zeizig\Moodle\Models\Course;

class CharonsController extends Controller
{
    /** @var CharonRepository */
    private $charonRepository;

    /**
     * CharonsController constructor.
     *
     * @param Request $request
     * @param CharonRepository $charonRepository
     */
    public function __construct(Request $request, CharonRepository $charonRepository)
    {
        parent::__construct($request);
        $this->charonRepository = $charonRepository;
    }

    /**
     * Get Charons by course.
     *
     * @param  Course $course
     *
     * @return \Illuminate\Database\Eloquent\Collection|Charon[]
     */
    public function getByCourse(Course $course)
    {
        $charons = $this->charonRepository->findCharonsByCourse($course->id);

        return $charons;
    }

    /**
     * @param int $charonId
     */
    public function deleteById(int $charonId)
    {
        Log::info('delete charon with id', [$charonId]);
        $this->charonRepository->deleteByInstanceId($charonId);
    }

    /**
     * Save Charon defense stuff.
     *
     * @param Charon $charon
     * @return Charon
     */
    public function saveCharonDefendingStuff(Charon $charon)
    {
        return $this->charonRepository->saveCharonDefendingStuff($charon, $this->request['defense_deadline'],
            $this->request['defense_duration'], $this->request['defense_labs'], $this->request['choose_teacher'], $this->request['defense_threshold']);
    }
}
