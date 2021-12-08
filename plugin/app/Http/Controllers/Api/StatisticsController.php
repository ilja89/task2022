<?php

namespace TTU\Charon\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Repositories\StatisticsRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\StatisticsService;

/**
 * Class SubmissionsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class StatisticsController extends Controller
{
    /** @var SubmissionsRepository */
    private $statisticsRepository;

    /** @var StatisticsService */
    private $statisticsService;

    /**
     * SubmissionsController constructor.
     *
     * @param Request $request
     * @param StatisticsRepository $statisticsRepository
     */
    public function __construct(
        Request $request,
        StatisticsRepository $statisticsRepository,
        StatisticsService $statisticsService
    ) {
        parent::__construct($request);
        $this->statisticsRepository = $statisticsRepository;
        $this->statisticsService = $statisticsService;
    }

    /**
     * Find all counts of submission dates for charon.
     *
     * @param int $charonId
     * @param int $courseId
     *
     * @return array
     * @throws Exception
     */
    public function getSubmissionDatesCountsForCharon(int $courseId, int $charonId)
    {
        return $this->statisticsService->findSubmissionDatesCountsForCharon($charonId);
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

    /**
     * Find all required general information for given charon
     *
     * @param int $courseId
     * @param int $charonId
     *
     * @return false|string
     */
    public function getCharonGeneralInformation(int $courseId, int $charonId) {
        return $this->statisticsService->getCharonGeneralInformation($courseId, $charonId);
    }
}
