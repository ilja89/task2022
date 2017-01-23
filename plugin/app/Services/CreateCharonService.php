<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;

use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
use Zeizig\Moodle\Models\GradeItem;
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

    /**
     * CreateCharonService constructor.
     *
     * @param  GradebookService $gradebookService
     * @param  GrademapService  $grademapService
     */
    public function __construct(GradebookService $gradebookService, GrademapService $grademapService)
    {
        $this->gradebookService = $gradebookService;
        $this->grademapService = $grademapService;
    }

    /**
     * Create a category for the given Charon.
     *
     * @param  Charon  $charon
     * @param  integer  $courseId
     *
     * @return int
     */
    public function addCategoryForCharon(Charon $charon, $courseId)
    {
        return $this->gradebookService->addGradeCategory($courseId, $charon->project_folder);
    }

    /**
     * Save Grademaps from the current request.
     * Assumes that these request parameters are set:
     *      grademaps (where tester_type_code => grademap)
     *          grademap_name
     *          max_points
     *          id_number
     *      course (automatically done by Moodle after submitting form)
     *
     * @param  Request  $request
     * @param  Charon  $charon
     *
     * @return void
     */
    public function saveGrademapsFromRequest(Request $request, Charon $charon)
    {
        foreach ($request->grademaps as $grade_type_code => $grademap) {
            // TODO: Add grade items to correct category.
            $this->grademapService->createGrademapWithGradeItem($charon, $grade_type_code, $request->course, $grademap);
        }
    }

    /**
     * Save deadlines from the current request.
     * Deadline times are saved in UTC.
     *
     * @param  Request  $request
     * @param  Charon  $charon
     *
     * @return void
     */
    public function saveDeadlinesFromRequest(Request $request, $charon)
    {
        if ($request->deadlines === null) {
            return;
        }

        foreach ($request->deadlines as $deadline) {
            if (!$this->correctDeadline($deadline)) {
                continue;
            }

            $deadlineTime = Carbon::createFromFormat('d-m-Y H:i', $deadline['deadline_time'], config('app.timezone'));
            $deadlineTime->setTimezone('UTC');
            $charon->deadlines()->save(new Deadline([
                'deadline_time' => $deadlineTime,
                'percentage' => $deadline['percentage'],
//                'group_id' => $deadline['group_id']
            ]));
        }
    }

    /**
     * Checks if the given deadline is correct. If it isn't, this method will return false.
     *
     * @param  array  $deadline
     *
     * @return bool
     */
    private function correctDeadline($deadline)
    {
        return $deadline['deadline_time'] !== null && $deadline['deadline_time'] !== '' && !is_numeric($deadline['percentage']);
    }
}
