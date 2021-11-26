<?php

namespace TTU\Charon\Services;

use Illuminate\Http\Request;
use TTU\Charon\Events\CharonUpdated;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Listeners\UpdateCalendarDeadlines;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Repositories\DeadlinesRepository;
use TTU\Charon\Repositories\StudentsRepository;
use Zeizig\Moodle\Services\CalendarService;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class UpdateCharonService.
 *
 * @package TTU\Charon\Services
 */
class UpdateCharonService
{
    /** @var GrademapService */
    protected $grademapService;
    /** @var GradebookService */
    protected $gradebookService;
    /** @var DeadlineService */
    protected $deadlineService;
    /** @var CharonGradingService */
    protected $charonGradingService;
    /** @var DeadlinesRepository */
    private $deadlinesRepository;
    /** @var CalendarService */
    private $calendarService;
    /** @var StudentsRepository */
    private $studentsRepository;
    /** @var SubmissionService */
    private $submissionService;

    /**
     * UpdateCharonService constructor.
     *
     * @param GrademapService $grademapService
     * @param GradebookService $gradebookService
     * @param DeadlineService $deadlineService
     * @param DeadlinesRepository $deadlinesRepository
     * @param CharonGradingService $charonGradingService
     * @param CalendarService $calendarService
     * @param StudentsRepository $studentsRepository
     * @param SubmissionService $submissionService
     */
    public function __construct(
        GrademapService $grademapService,
        GradebookService $gradebookService,
        DeadlineService $deadlineService,
        DeadlinesRepository $deadlinesRepository,
        CharonGradingService $charonGradingService,
        CalendarService $calendarService,
        StudentsRepository $studentsRepository,
        SubmissionService $submissionService
    ) {
        $this->grademapService      = $grademapService;
        $this->gradebookService     = $gradebookService;
        $this->deadlineService      = $deadlineService;
        $this->deadlinesRepository  = $deadlinesRepository;
        $this->charonGradingService = $charonGradingService;
        $this->calendarService      = $calendarService;
        $this->studentsRepository   = $studentsRepository;
        $this->submissionService    = $submissionService;
    }

    /**
     * Update Grademaps with info from the request.
     * This assumes that deadlines are updated before this so grades
     * can be recalculated if needed.
     *
     * @param array $newGrademaps
     * @param Charon $charon
     * @param bool $deadlinesWereUpdated
     * @param bool $recalculateGrades
     *
     * @return void
     */
    public function updateGrademaps(
        array $newGrademaps,
        Charon $charon,
        bool $deadlinesWereUpdated = false,
        bool $recalculateGrades = false
    ) {
        $grademaps = $charon->grademaps;

        // Check previous Grademaps.
        foreach ($grademaps as $grademap) {
            $newGrademap = $this->getGrademapByGradeType($newGrademaps, $grademap->grade_type_code);

            $newGrademaps[$grademap->grade_type_code]['checked'] = true;
            $this->updateExistingGrademap($grademap, $newGrademap, $deadlinesWereUpdated, $recalculateGrades);
        }

        // Check the rest of the Grademaps.
        foreach ($newGrademaps as $gradeType => $newGrademap) {
            if ( ! isset($newGrademap['checked']) || ! $newGrademap['checked']) {
                $this->grademapService->createGrademapWithGradeItem(
                    $charon, $gradeType, $charon->course, $newGrademap
                );
            }
        }

        if ($recalculateGrades) {
            $this->updateActiveSubmissions($charon);
        }
    }

    /**
     * Updates Deadlines with info from the request.
     *
     * @param Request $request
     * @param Charon $charon
     * @param string $userTimezone
     *
     * @return bool
     */
    public function updateDeadlines(Request $request, Charon $charon, string $userTimezone)
    {
        $oldDeadlines = $charon->deadlines;
        $charonId = $charon->id;

        $this->deadlinesRepository->deleteAllDeadlinesForCharon($charonId);
        $this->deadlinesRepository->deleteAllCalendarEventsForCharon($charonId);

        // Create new deadlines
        if ($request->deadlines !== null) {
            foreach ($request->deadlines as $deadline) {
                $this->deadlineService->createDeadline($charon, $deadline);
            }
        }
        $charon->load('deadlines');
        $event = new CharonUpdated($charon);
        $eventAdder = new UpdateCalendarDeadlines($this->calendarService, new MoodleConfig());
        $eventAdder->handle($event, $userTimezone);
        return $this->deadlinesAreNew($oldDeadlines, $charon->deadlines);
    }

    /**
     * Updates the Category calculation formula and max score for the given Charon.
     *
     * @param  Charon $charon
     * @param  Request $request
     *
     * @return void
     */
    public function updateCategoryCalculationAndMaxScore(Charon $charon, $request)
    {
        if ($charon->category_id == null || !$request->has('max_score')) {
            return;
        }

        $gradeItem = $this->gradebookService->getGradeItemByCategoryId($charon->category_id);
        $gradeItem->grademax = $request['max_score'];

        $gradeItem->calculation = $request->has('calculation_formula') && strlen($request['calculation_formula']) > 1
            ? $this->gradebookService->normalizeCalculationFormula($request['calculation_formula'], $charon->course)
            : $request['calculation_formula'];

        $gradeItem->save();
    }

    /**
     * Given an old Grademap and an array for the new Grademap will update
     * the old Grademap with new info or delete the Grademap if there is no new
     * Grademap.
     *
     * New grademap fields: grademap_name, max_points, id_number.
     *
     * @param  Grademap $grademap
     * @param  array $newGrademap
     * @param  bool $deadlinesWereUpdated
     * @param  bool $recalculateGrades
     *
     * @return Grademap
     */
    private function updateExistingGrademap($grademap, $newGrademap, $deadlinesWereUpdated, $recalculateGrades)
    {
        if ($newGrademap === null) {
            $this->grademapService->deleteGrademap($grademap);

            return null;
        }

        $grademap->name = $newGrademap['grademap_name'];
        $grademap->persistent = $grademap->grade_type_code > 1000 && isset($newGrademap['persistent']) && $newGrademap['persistent'] === '1';
        $grademap->save();

        $oldMax = $grademap->gradeItem->grademax;
        $this->gradebookService->updateGradeItem($grademap->grade_item_id, [
            'itemname' => $newGrademap['grademap_name'],
            'grademax' => $newGrademap['max_points'],
            'idnumber' => $newGrademap['id_number'],
        ]);

        if ($recalculateGrades && ($oldMax != $newGrademap['max_points'] || $deadlinesWereUpdated)) {
            if ($grademap->isTestsGrade() && $grademap->charon->gradingMethod->isPreferBestEachTestGrade()) {
                $this->charonGradingService->resetGradesCalculatedResults($grademap);
            }
            $this->charonGradingService->recalculateGrades($grademap);
        }

        return $grademap;
    }

    /**
     * Gets the Grademap with the given Grade type from given grademaps.
     * The grademaps come from the request so it's a map where grade type => grademap info.
     *
     * @param  array $grademaps
     * @param  integer $gradeTypeCode
     *
     * @return array
     */
    private function getGrademapByGradeType($grademaps, $gradeTypeCode)
    {
        foreach ($grademaps as $gradeType => $grademap) {
            if ($gradeType == $gradeTypeCode) {
                return $grademap;
            }
        }

        return null;
    }

    /**
     * @param $oldDeadlines
     * @param $newDeadlines
     *
     * @return bool
     */
    private function deadlinesAreNew($oldDeadlines, $newDeadlines)
    {
        // Check if the count is different
        if ($oldDeadlines->count() !== $newDeadlines->count()) {
            return true;
        }

        // Loop through both and check. n^2
        foreach ($oldDeadlines as $oldDeadline) {
            $found = false;
            foreach ($newDeadlines as $newDeadline) {
                /** @var Deadline $oldDeadline */
                /** @var Deadline $newDeadline */
                if ($oldDeadline->percentage === $newDeadline->percentage
                    && $oldDeadline->deadline_time->timestamp === $newDeadline->deadline_time->timestamp
                    && $oldDeadline->group_id === $newDeadline->group_id) {
                    $found = true;
                }
            }

            if (!$found) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update active submissions for students that are on given charon's course
     *
     * @param Charon $charon
     */
    public function updateActiveSubmissions(Charon $charon): void
    {
        $gradingMethod = $charon->gradingMethod;
        $students = $this->studentsRepository
            ->searchUsersByCourseKeywordAndRole($charon->course, null, ["student"])->all();

        foreach ($students as $student) {

            if ($this->charonGradingService->hasConfirmedSubmission($charon->id, $student->id)) {
                continue;
            }

            if ($gradingMethod->isPreferBest() || $gradingMethod->isPreferBestEachTestGrade()) {
                $submission = $this->submissionService->findUsersBestSubmission($charon->id, $student->id);
            } else if ($gradingMethod->isPreferLast()) {
                $submission = $this->submissionService->findUsersLatestSubmission($charon->id, $student->id);
            } else {
                throw new \RuntimeException("given charon has an unknown grading method");
            }

            if ($submission !== null) {
                $this->charonGradingService->updateGrades($submission, $student->id);
            }
        }
    }
}
