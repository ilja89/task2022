<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TTU\Charon\Exceptions\ResultPointsRequiredException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\StatisticsRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\Flows\TeacherModifiesSubmission;
use TTU\Charon\Services\SubmissionService;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;

/**
 * Class SubmissionsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class StatisticsController extends Controller
{
    /** @var SubmissionsRepository */
    private $statisticsRepository;

    /**
     * SubmissionsController constructor.
     *
     * @param Request $request
     * @param StatisticsRepository $statisticsRepository
     */
    public function __construct(
        Request $request,
        StatisticsRepository $statisticsRepository
    ) {
        parent::__construct($request);
        $this->statisticsRepository = $statisticsRepository;
    }

    /**
     * Find all counts of submission dates for charon.
     *
     * @param int $charonId
     * @param int $courseId
     *
     * @return array
     */
    public function getSubmissionDatesCountsForCharon(int $courseId, int $charonId)
    {
        return $this->statisticsRepository->findSubmissionDatesCountsForCharon($charonId);
    }

    /**
     * Get list of submissions for current day per 30 minutes.
     *
     * @param int $courseId
     * @param int $charonId
     *
     * @return array
     */
    public function getSubmissionCountsForCharonToday(int $courseId, int $charonId)
    {
        return $this->statisticsRepository->findSubmissionCountsToday($charonId);
    }
}
