<?php

namespace TTU\Charon\Repositories;

use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
use TTU\Charon\Facades\MoodleConfig;


/**
 * Class SubmissionsRepository.
 *
 * @package TTU\Charon\Repositories
 */
class StatisticsRepository
{

    /** @var MoodleConfig */
    private $moodleConfig;

    /**
     * @param MoodleConfig $moodleConfig
     */
    public function __construct(MoodleConfig $moodleConfig)
    {
        $this->moodleConfig = $moodleConfig;
    }

    /**
     * Find all submission dates with counts for the charon with the given id.
     *
     * @param int $charonId
     *
     * @return array
     * @throws Exception
     */
    public function findSubmissionDatesCountsForCharon(int $charonId)
    {
        $prefix = $this->moodleConfig->prefix;

        $lastYear = date("Y-12-31", strtotime("-1 years"));
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

        $firstSubmissionDays = (int) date_diff($lastYearDateTime, $firstSubmissionDate)->format('%a');
        $lastSubmissionDays = $firstSubmissionDays + (int) date_diff($firstSubmissionDate, $lastSubmissionDate)->format('%a');
        return DB::select(DB::raw(
            "(SELECT DATE(created_at) AS dateRow, COUNT(DATE(created_at)) AS count
            FROM " . $prefix . "charon_submission
            WHERE charon_id = " . $charonId . "
            GROUP BY dateRow)

            UNION (SELECT * FROM

            (SELECT '" . $lastYear . "' + INTERVAL seq DAY AS dateRow, 0 AS count
            FROM seq_" . $firstSubmissionDays . "_to_" . $lastSubmissionDays . ") AS seqTable

            WHERE seqTable.dateRow NOT IN (
            SELECT DATE(created_at)
            FROM " . $prefix . "charon_submission
            WHERE charon_id = " . $charonId . "))

            ORDER BY dateRow;
            "
        ));
    }

    /**
     * Find all submission for current day and list them by 30 min.
     *
     * @param int $charonId
     *
     * @return array
     */
    public function findSubmissionCountsToday(int $charonId)
    {
        $prefix = $this->moodleConfig->prefix;

        return DB::select(DB::raw(
            "(SELECT TIME(
        DATE_ADD(
        DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00'),
        INTERVAL IF(MINUTE(created_at) < 30, 00, 30) MINUTE
        )) AS time, COUNT(*) AS count
        
        FROM " . $prefix . "charon_submission
        WHERE DATE(created_at) = CURDATE() AND charon_id = " . $charonId . "
        GROUP BY time)
        
        UNION
        
        (SELECT * FROM 
        
        (SELECT TIME('00-00-00') + INTERVAL (seq) MINUTE AS time, 0 AS count
        FROM seq_0_to_1410_step_30) AS table2
        WHERE table2.time NOT IN (
        SELECT TIME(DATE_ADD(
        DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00'),
        INTERVAL IF(MINUTE(created_at) < 30, 00, 30) MINUTE)) AS time
        FROM " . $prefix . "charon_submission
        WHERE DATE(created_at) = CURDATE() AND charon_id = " . $charonId . ")
        
        GROUP BY table2.time)
        
        ORDER BY time;
        "
        ));
    }

    /**
     * Find all required general information for given charon
     *
     * @param int $charonId
     *
     * @return false|string
     */
    public function getCharonGeneralInformation(int $charonId)
    {
        $generalInformation = new stdClass();
        $defenseItemNumber = 1001;

        $categoryId = $this->findCharonCategoryId($charonId);
        $defenseGradeItemIds = $this->findDefenseGradeItemIds($categoryId, $defenseItemNumber);
        $studentsStarted = $this->findStudentsStartedAmount($charonId);
        $studentsDefended = $this->findStudentsDefendedAmount($defenseGradeItemIds);
        $avgDefenseGrade = $this->findAverageDefenseGrade($defenseGradeItemIds);

        $generalInformation->studentsStarted = $studentsStarted;
        $generalInformation->studentsDefended = $studentsDefended;
        $generalInformation->avgDefenseGrade = $avgDefenseGrade;

        return json_encode($generalInformation);
    }

    function findCharonCategoryId($charonId)
    {
        return DB::table('charon')
            ->where('id', $charonId)
            ->value('category_id');
    }

    function findDefenseGradeItemIds($categoryId, $defenseItemNumber): \Illuminate\Support\Collection
    {
        return DB::table('grade_items')
            ->select('id')
            ->whereNotNull('categoryid')
            ->where('categoryid', $categoryId)
            ->where('itemnumber', $defenseItemNumber)
            ->pluck('id');
    }

    function findStudentsStartedAmount($charonId): int
    {
        return DB::table('charon_submission')
            ->where('charon_id', $charonId)
            ->distinct()
            ->count('user_id');
    }

    function findStudentsDefendedAmount($defenseGradeItemIds)
    {
        return DB::table('grade_grades')
            ->whereIn('itemid', $defenseGradeItemIds)
            ->whereNotNull('finalgrade')
            ->where('finalgrade', '>', 0)
            ->count();
    }

    function findAverageDefenseGrade($defenseGradeItemIds)
    {
        return DB::table('grade_grades')
            ->whereIn('itemid', $defenseGradeItemIds)
            ->whereNotNull('finalgrade')
            ->where('finalgrade', '>', 0)
            ->avg('finalgrade');
    }
}
