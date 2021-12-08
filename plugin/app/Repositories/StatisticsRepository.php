<?php

namespace TTU\Charon\Repositories;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     * @param $lastYear
     * @param $firstSubmissionDays
     * @param $lastSubmissionDays
     * @return array
     */
    public function findSubmissionDatesCountsForCharon(int $charonId, $lastYear, $firstSubmissionDays, $lastSubmissionDays): array
    {
        $prefix = $this->moodleConfig->prefix;

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
     * Finds category id of charon
     * @param $charonId
     * @return int
     */
    function findCharonCategoryId($charonId): int
    {
        return DB::table('charon')
            ->where('id', $charonId)
            ->value('category_id');
    }

    /**
     * Finds grade item ids for given charon category id
     * @param $courseId
     * @param $categoryId
     * @return int|null
     */
    function findCategoryGradeItemId($courseId, $categoryId): ?int
    {
        return DB::table('grade_items')
            ->where('iteminstance', $categoryId)
            ->where('courseid', $courseId)
            ->where('itemtype', 'category')
            ->value('id');
    }

    /**
     * Finds amount of students started with charon
     * @param $charonId
     * @return int
     */
    function findStudentsStartedAmount($charonId): int
    {
        return DB::table('charon_submission')
            ->where('charon_id', $charonId)
            ->distinct()
            ->count('user_id');
    }

    /**
     * Find user ids that have confirmed submission on given charon
     * @param $charonId
     * @return Collection
     *
     */
    function findUserIdsWithConfirmedSubmissions($charonId): Collection
    {
        return DB::table('charon_submission')
            ->where('confirmed', '=', 1)
            ->where('charon_id', '=', $charonId)
            ->pluck('user_id');
    }

    /**
     * Find amount of students that have defended
     * @param $charonId
     * @return int
     */
    function findStudentsDefendedAmount($charonId): int
    {
        return DB::table('charon_submission')
            ->where('charon_id', $charonId)
            ->where('confirmed', '=', 1)
            ->count();
    }

    /**
     * Find average category grade for charon
     * In other words, finds average grade for defended students
     * @param $charonId
     * @param $categoryGradeItemId
     * @return mixed|null
     */
    function findAverageCategoryGrade($charonId, $categoryGradeItemId)
    {
        if ($categoryGradeItemId == null) {
            return null;
        }
        $confirmedUserIds = $this->findUserIdsWithConfirmedSubmissions($charonId);
        return DB::table('grade_grades')
            ->whereIn('userid', $confirmedUserIds)
            ->where('itemid', $categoryGradeItemId)
            ->avg('finalgrade');
    }

    /**
     * Find category max points for charon
     * @param $categoryId
     * @return mixed|null
     */
    function findCharonMaxPoints($categoryId)
    {
        return DB::table('grade_items')
            ->where('iteminstance', $categoryId)
            ->where('itemtype', 'category')
            ->value('grademax');
    }

    /**
     * Find all charon deadlines with percentages
     * @param $charonId
     * @return Collection
     */
    function findCharonDeadlinesWithPercentages($charonId)
    {
        return DB::table('charon_deadline')
            ->where('charon_id', $charonId)
            ->select('deadline_time', 'percentage')
            ->orderBy('deadline_time')
            ->get();
    }

    /**
     * Find grade item for a specific grade (test, style, defense)
     * This is not meant for category grade!
     * Valid itemnumber values are 1, 101, 1001 (tests, style, defense)
     * @param $courseId
     * @param $categoryId
     * @param $itemNumber
     * @return Object|null
     */
    function findGradeItemSubcategory($courseId, $categoryId, $itemNumber)
    {
        if (!in_array($itemNumber, ['1', '101', '1001'])) {
            return null;
        }
        return DB::table('grade_items')
            ->where('categoryid', $categoryId)
            ->where('itemnumber', $itemNumber)
            ->where('courseid', $courseId)
            ->first();
    }

    /**
     * Find highest score (tests) so far for given charon
     * @param $courseId
     * @param $charonId
     * @return mixed
     */
    function findHighestScoreForCharon($courseId, $charonId)
    {
        $testsItemNumber = 1;
        $categoryId = $this->findCharonCategoryId($charonId);

        $gradeItemTests = $this->findGradeItemSubcategory($courseId, $categoryId, $testsItemNumber);

        if (!$gradeItemTests) {
            return null;
        }

        return DB::table('grade_grades')
            ->where('itemid', $gradeItemTests->id)
            ->max('finalgrade');
    }
}
