<?php

namespace TTU\Charon\Repositories;

use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
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

        $firstSubmission = DB::select(DB::raw(
            "SELECT DATE(created_at) AS date1 FROM mdl_charon_submission WHERE charon_id = " . $charonId . " ORDER BY date1 LIMIT 0,1"
        ));

        $date1 = array_pop($firstSubmission)->date1;
        $firstSubmissionDate = new DateTime($date1);

        $lastSubmission =DB::select(DB::raw(
            "SELECT DATE(created_at) AS date2 FROM mdl_charon_submission WHERE charon_id = " . $charonId . " ORDER BY date2 DESC LIMIT 0,1"
        ));

        $date2 = array_pop($lastSubmission)->date2;
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
        return DB::select(DB::raw(
            "(SELECT TIME(
        DATE_ADD(
        DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00'),
        INTERVAL IF(MINUTE(created_at) < 30, 00, 30) MINUTE
        )) AS time, COUNT(*) AS count
        
        FROM mdl_charon_submission
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
        FROM mdl_charon_submission
        WHERE DATE(created_at) = CURDATE() AND charon_id = " . $charonId . ")
        
        GROUP BY table2.time)
        
        ORDER BY time;
        "
        ));
    }
}
