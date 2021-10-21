<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Services\CharonService;
use TTU\Charon\Services\LogParseService;
use Zeizig\Moodle\Models\Course;

class CharonsController extends Controller
{
    /** @var CharonRepository */
    private $charonRepository;

    /** @var CharonService */
    private $charonService;

    /** @var LogParseService */
    private $logParser;

    /**
     * CharonsController constructor.
     *
     * @param Request $request
     * @param CharonRepository $charonRepository
     * @param CharonService $charonService
     * @param LogParseService $logParser
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        CharonService $charonService,
        LogParseService $logParser
    ) {
        parent::__construct($request);

        $this->charonRepository = $charonRepository;
        $this->charonService = $charonService;
        $this->logParser = $logParser;
    }

    public function getByCourse(Course $course): array
    {
        return $this->charonService->findCharonsByCourseId($course->id);
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
     * @param Charon $charon
     * @return Charon
     */
    public function saveCharon(Charon $charon)
    {
        $modifiableFields = [
            'name', 'project_folder',
            'defense_duration', 'defense_threshold', 'defense_start_time', 'defense_deadline', 'group_size', 'choose_teacher',
            'docker_timeout', 'docker_content_root', 'docker_test_root', 'tester_extra', 'system_extra', 'tester_type_code'
        ];

        Log::info('Updating Charon:', [$this->request->toArray()]);
        return $this->charonRepository->saveCharon($charon, $this->request->toArray(), $modifiableFields);
    }

    public function getFull(Request $request)
    {
        $id = $request->route('charon');
        return Charon::where('id', '=', $id)->get()[0];
    }
}
