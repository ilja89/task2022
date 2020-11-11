<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Services\LogParseService;
use Zeizig\Moodle\Models\Course;

class CharonsController extends Controller
{
    /** @var CharonRepository */
    private $charonRepository;

    /** @var LogParseService */
    private $logParser;

    /**
     * CharonsController constructor.
     *
     * @param Request $request
     * @param CharonRepository $charonRepository
     * @param LogParseService $logParser
     */
    public function __construct(Request $request, CharonRepository $charonRepository, LogParseService $logParser)
    {
        parent::__construct($request);

        $this->charonRepository = $charonRepository;
        $this->logParser = $logParser;
    }

    /**
     * Get Charons by course.
     *
     * @param Course $course
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
        $this->charonRepository->deleteByInstanceId($charonId);
    }

    /**
     * @param int $courseId
     * @return String
     */
    public function getLogsById(int $courseId)
    {
        return $this->logParser->readLogs();
    }

    /**
     * Save Charon defense stuff.
     *
     * @param Charon $charon
     * @return Charon
     */
    public function saveCharon(Charon $charon)
    {
        return $this->charonRepository->saveCharon($charon, $this->request->toArray());
    }

    public function getFull(Request $request)
    {
        $id = $request->route('charon');
        return Charon::where('id', '=', $id)->get()[0];
    }
}
