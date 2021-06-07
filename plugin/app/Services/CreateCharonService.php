<?php

namespace TTU\Charon\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
use Zeizig\Moodle\Services\CalendarService;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class CreateCharonService.
 *
 * @package TTU\Charon\Services
 */
class CreateCharonService
{
    /** @var GradebookService */
    protected $gradebookService;

    /** @var GrademapService */
    protected $grademapService;

    /** @var DeadlineService */
    protected $deadlineService;

    /** @var CharonDefenseLabService */
    protected $charonDefenseLabService;

    /**
     * CreateCharonService constructor.
     *
     * @param  GradebookService $gradebookService
     * @param  GrademapService $grademapService
     * @param  DeadlineService $deadlineService
     * @param  CharonDefenseLabService $charonDefenseLabService
     */
    public function __construct(
        GradebookService $gradebookService,
        GrademapService $grademapService,
        DeadlineService $deadlineService,
        CharonDefenseLabService $charonDefenseLabService
    ) {
        $this->gradebookService = $gradebookService;
        $this->grademapService  = $grademapService;
        $this->deadlineService  = $deadlineService;
        $this->charonDefenseLabService = $charonDefenseLabService;
    }

    /**
     * Create a category for the given Charon.
     *
     * @param  Charon $charon
     * @param  integer $courseId
     *
     * @return int
     */
    public function addCategoryForCharon(Charon $charon, $courseId)
    {
        return $this->gradebookService->addGradeCategory($courseId, $charon->name);
    }

    /**
     * Save Grademaps from the current request.
     * Assumes that these request parameters are set:
     *      grademaps (where grade_type_code => grademap)
     *          grademap_name
     *          max_points
     *          id_number
     *      course (automatically done by Moodle after submitting form)
     *
     * @param  Request $request
     * @param  Charon $charon
     *
     * @return void
     */
    public function saveGrademapsFromRequest($request, Charon $charon)
    {
        if (!$request->has('grademaps')) {
            return;
        }

        foreach ($request->input('grademaps') as $grade_type_code => $grademap) {
            $this->grademapService->createGrademapWithGradeItem($charon, $grade_type_code, $request->course, $grademap);
        }
    }

    /**
     * Save deadlines from the current request.
     * Deadline times are saved in UTC.
     *
     * @param  Request $request
     * @param  Charon $charon
     *
     * @return void
     */
    public function saveDeadlinesFromRequest(Request $request, $charon)
    {
        if ($request->deadlines === null) {
            return;
        }

        $charonId = $charon->id;
        $courseId = $charon->course;
        $charonName = $charon->name;
        $event = new CalendarService();
        foreach ($request->deadlines as $deadline) {
            $this->deadlineService->createDeadline($charon, $deadline);

            $percentage = $deadline['percentage'];
            $name = $charonName . ' ' . $percentage .'%' ;
            $time = $deadline['deadline_time'];
            $deadlineTime = strtotime($time);
            $description = 'deadline for ' . $charonName . ': ' . $percentage . '% after ' . $time;

            $event->createCharonDeadlineEvent($name, $description, $courseId, $charonId, $deadlineTime);
        }
    }

    /**
     * Save defense labs from the current request.
     *
     * @param  Request $request
     * @param  Charon $charon
     *
     * @return void
     */
    public function saveDefenseLabsFromRequest(Request $request, $charon)
    {
        if ($request->defenseLabs === null) {
            return;
        }

        foreach ($request->defenseLabs as $defenseLab) {
            $this->charonDefenseLabService->createCharonDefenseLab($charon, $defenseLab);
        }
    }
}
