<?php

namespace TTU\Charon\Services;

use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use stdClass;
use TTU\Charon\Repositories\StatisticsRepository;

class StatisticsService
{
    /** @var StatisticsRepository */
    private $statisticsRepository;

    /**
     * @param StatisticsRepository $statisticsRepository
     */
    public function __construct(StatisticsRepository $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    /**
     * Find all required general information for given charon
     * @param int $charonId
     * @return false|string
     */
    public function getCharonGeneralInformation(int $courseId, int $charonId)
    {
        $generalInformation = new stdClass();

        $categoryId = $this->statisticsRepository->findCharonCategoryId($charonId);
        $categoryGradeItemId = $this->statisticsRepository->findCategoryGradeItemId($courseId, $categoryId);

        $studentsStarted = $this->statisticsRepository->findStudentsStartedAmount($charonId);
        $studentsDefended = $this->statisticsRepository->findStudentsDefendedAmount($charonId);
        $avgCategoryGrade = $this->statisticsRepository->findAverageCategoryGrade($charonId, $categoryGradeItemId);
        $maxPoints = $this->statisticsRepository->findCharonMaxPoints($categoryId);
        $deadlines = $this->statisticsRepository->findCharonDeadlinesWithPercentages($charonId);
        $highestScore = $this->statisticsRepository->findHighestScoreForCharon($courseId, $charonId);

        $generalInformation->studentsStarted = $studentsStarted;
        $generalInformation->studentsDefended = $studentsDefended;
        $generalInformation->avgDefenseGrade = $avgCategoryGrade;
        $generalInformation->maxPoints = $maxPoints;
        $generalInformation->deadlines = $deadlines;
        $generalInformation->highestScore = $highestScore;

        return json_encode($generalInformation);
    }

    /**
     * Find all submission dates with counts for the charon with the given id.
     *
     * @param int $charonId
     *
     * @return array
     * @throws Exception
     */
    public function findSubmissionDatesCountsForCharon(int $charonId): array
    {
        $lastYear = date("Y-m-d", strtotime("-1 years"));
        $lastYearDateTime = new DateTime($lastYear);

        $firstSubmission = DB::table('charon_submission')
            ->select('created_at')
            ->where('charon_id', $charonId)
            ->orderBy('created_at')
            ->first();

        if ($firstSubmission == null) {
            return [];
        }

        $date1 = $firstSubmission->created_at;
        $firstSubmissionDate = new DateTime($date1);

        $lastSubmission = DB::table('charon_submission')
            ->select('created_at')
            ->where('charon_id', $charonId)
            ->orderByDesc('created_at')
            ->first();

        $date2 = $lastSubmission->created_at;
        $lastSubmissionDate = new DateTime($date2);

        $firstSubmissionDays = (int)date_diff($lastYearDateTime, $firstSubmissionDate)->format('%a');
        $lastSubmissionDays = $firstSubmissionDays + (int)date_diff($firstSubmissionDate, $lastSubmissionDate)->format('%a');

        return $this->statisticsRepository->findSubmissionDatesCountsForCharon($charonId, $lastYear, $firstSubmissionDays, $lastSubmissionDays);
    }
}