<?php

namespace TTU\Charon\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\TestSuite;
use TTU\Charon\Models\UnitTest;

/**
 * Class SubmissionsRepository.
 *
 * @package TTU\Charon\Repositories
 */
class SubmissionsRepository
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
     * Find submission by its ID. Leave out stdout, stderr because it might be too big.
     *
     * @param int $submissionId
     * @param int[] $gradeTypeCodes
     *
     * @return Submission
     */
    public function findById($submissionId, $gradeTypeCodes)
    {
        $fields = [
            'id',
            'charon_id',
            'confirmed',
            'created_at',
            'git_hash',
            'git_commit_message',
            'git_timestamp',
            'user_id',
            'mail',
            'grader_id',
            'stdout',
            'stderr'
        ];

        return Submission::with([
            'results' => function ($query) use ($gradeTypeCodes) {
                // Only select results which have a corresponding grademap
                $query->whereIn('grade_type_code', $gradeTypeCodes);
                $query->orderBy('grade_type_code');
            },
            'grader' => function ($query) {
                $query->select(['id', 'firstname', 'lastname', 'email', 'idnumber', 'username']);
            },
            'users' => function ($query) {
                $query->select(['id', 'firstname', 'lastname', 'username']);
            }
        ])->where('id', $submissionId)->first($fields);
    }

    /**
     * @param int $userId
     *
     * @return Collection|Submission[]
     */
    public function findUserSubmissions(int $userId) {
        $submissions = $this->buildForUser($userId)
            ->select('charon_submission.*')
            ->get()
            ->toArray();

        return Submission::hydrate($submissions);
    }

    /**
     * Find submissions by charon and user. Also paginates this info by 5.
     *
     * Only select results which have a corresponding grademap.
     *
     * @param Charon $charon
     * @param int $userId
     *
     * @return Paginator
     */
    public function paginateSubmissionsByCharonUser(Charon $charon, int $userId)
    {
        $fields = [
            'charon_submission.id',
            'charon_submission.charon_id',
            'charon_submission.confirmed',
            'charon_submission.created_at',
            'charon_submission.git_hash',
            'charon_submission.git_timestamp',
            'charon_submission.git_commit_message',
            'charon_submission.user_id',
            'charon_submission.mail',
        ];

        $submissions = $this->buildForUser($userId)
            ->where('charon_submission.charon_id', $charon->id)
            ->orderBy('charon_submission.created_at', 'desc')
            ->orderBy('charon_submission.git_timestamp', 'desc')
            ->select($fields)
            ->simplePaginate(5);

        $submissions->appends(['user_id' => $userId])->links();

        foreach ($submissions as $submission) {
            $submission->results = Result::where('submission_id', $submission->id)
                ->whereIn('grade_type_code', $charon->getGradeTypeCodes())
                ->select(['id', 'submission_id', 'calculated_result', 'grade_type_code', 'percentage'])
                ->orderBy('grade_type_code')
                ->get();
            $submission->test_suites = $this->getTestSuites($submission->id);
        }

        return $submissions;
    }

    /**
     * @param $submissionId
     * @return TestSuite[]
     */
    private function getTestSuites($submissionId)
    {
        $testSuites = \DB::table('charon_test_suite')
            ->where('submission_id', $submissionId)
            ->select('*')
            ->get();
        for ($i = 0; $i < count($testSuites); $i++) {
            $testSuites[$i]->unit_tests = $this->getUnitTestsResults($testSuites[$i]->id);
        }
        return $testSuites;
    }

    /**
     * @param $testSuiteId
     * @return UnitTest[]
     */
    private function getUnitTestsResults($testSuiteId)
    {
        return \DB::table('charon_unit_test')
            ->where('test_suite_id', $testSuiteId)
            ->select('*')
            ->get();
    }

    /**
     * Finds all submissions which are confirmed for given user and Charon.
     *
     * @param int $userId
     * @param int $charonId
     *
     * @return Submission[]
     */
    public function findConfirmedSubmissionsForUserAndCharon(int $userId, int $charonId)
    {
        $submissions = $this->buildForUser($userId)
            ->where('charon_id', $charonId)
            ->where('confirmed', 1)
            ->select('charon_submission.*')
            ->get()
            ->toArray();

        return Submission::hydrate($submissions);
    }

    /**
     * Finds all confirmed submissions for given user.
     *
     * @param int $courseId
     * @param int $userId
     *
     * @return array
     */
    public function findGradedCharonsByUser(int $courseId, int $userId)
    {
        $prefix = $this->moodleConfig->prefix;

        return DB::select(
            'SELECT ch.id, ch.name, gr_gr.finalgrade'
                . ' FROM ' . $prefix . 'charon ch'
                . ' LEFT JOIN ' . $prefix . 'grade_items gr_it ON gr_it.iteminstance = ch.category_id AND gr_it.itemtype = "category"'
                . ' LEFT JOIN ' . $prefix . 'grade_grades gr_gr ON gr_gr.itemid = gr_it.id'
                . ' WHERE ch.course = ? AND gr_gr.userid = ?',
            [$courseId, $userId]
        );
    }

    /**
     * Finds all course submissions and calculates each Charon average.
     *
     * TODO: rename to findCourseCharonAverageGrades?
     *
     * @param int $courseId
     *
     * @return array
     */
    public function findBestAverageCourseSubmissions(int $courseId)
    {
        $prefix = $this->moodleConfig->prefix;

        return DB::select(
            'SELECT ch.id, ch.name, gr_it.grademax, AVG(gr_gr.finalgrade) AS course_average_finalgrade'
                . ' FROM ' . $prefix . 'charon ch'
                . ' LEFT JOIN ' . $prefix . 'grade_items gr_it ON gr_it.iteminstance = ch.category_id AND gr_it.itemtype = "category"'
                . ' LEFT JOIN ' . $prefix . 'grade_grades gr_gr ON gr_gr.itemid = gr_it.id'
                . ' WHERE ch.course = ?'
                . ' GROUP BY ch.id, ch.name, gr_it.grademax',
            [$courseId]
        );
    }

    /**
     * Confirms the given submission.
     *
     * @param Submission $submission
     * @param int|null $graderId
     *
     * @return void
     */
    public function confirmSubmission(Submission $submission, $graderId = null)
    {
        $submission->confirmed = 1;
        $submission->grader_id = $graderId;
        $submission->save();
    }

    /**
     * Unconfirms the given submission.
     *
     * @param Submission $submission
     *
     * @return void
     */
    public function unconfirmSubmission(Submission $submission)
    {
        $submission->confirmed = 0;
        $submission->save();
    }

    /**
     * Check if the given user has any confirmed submissions for the given Charon.
     *
     * @param int $charonId
     * @param int $userId
     *
     * @return bool
     */
    public function charonHasConfirmedSubmissions(int $charonId, int $userId)
    {
        /** @var Collection $submissions */
        $submissions = $this->buildForUser($userId)
            ->where('charon_submission.charon_id', $charonId)
            ->where('charon_submission.confirmed', 1)
            ->select('charon_submission.id')
            ->get();

        return $submissions->isNotEmpty();
    }

    /**
     * Saves the given result.
     *
     * @param Result $result
     *
     * @return Result
     */
    public function saveResult(Result $result)
    {
        $result->save();
        return $result;
    }

    /**
     * Saves a new empty result with the given parameters.
     *
     * @param int $submissionId
     * @param int $gradeTypeCode
     * @param string $stdout
     *
     * @return void
     */
    public function saveNewEmptyResult($submissionId, $gradeTypeCode, $stdout = null)
    {
        if ($stdout === null) {
            $stdout = 'An empty result';
        }

        Result::create([
            'submission_id' => $submissionId,
            'grade_type_code' => $gradeTypeCode,
            'percentage' => 0,
            'calculated_result' => 0,
            'stdout' => $stdout,
        ]);
    }

    /**
     * @param int $submissionId
     * @param int $studentId
     * @param int $charonId
     * @param int $gradeTypeCode
     */
    public function carryPersistentResult(
        int $submissionId,
        int $studentId,
        int $charonId,
        int $gradeTypeCode
    ) {
        /** @var Result $previous */
        $previous = $this->buildForUser($studentId)
            ->join('charon_result', 'charon_submission.id', '=', 'charon_result.submission_id')
            ->select('charon_result.*')
            ->where('charon_submission.charon_id', $charonId)
            ->where('charon_submission.confirmed', 1)
            ->whereNotNull('charon_submission.grader_id')
            ->where('charon_result.grade_type_code', $gradeTypeCode)
            ->orderBy('charon_submission.updated_at', 'desc')
            ->first();

        if (!$previous) {
            $this->saveNewEmptyResult($submissionId, $gradeTypeCode);
            return;
        }

        Result::create([
            'submission_id' => $submissionId,
            'grade_type_code' => $gradeTypeCode,
            'percentage' => $previous->percentage,
            'calculated_result' => $previous->calculated_result,
            'stdout' => 'Carried over from Result ' . $previous->id,
        ]);
    }

    /**
     * Gets the order number of the given submission in course for student. So if this is the 3rd submission
     * this will return 3.
     *
     * @param Submission $submission
     * @param int $studentId
     *
     * @return int
     */
    public function getSubmissionCourseOrderNumber(Submission $submission, int $studentId)
    {
        return $this->buildForUser($studentId)
            ->where('charon_submission.git_timestamp', '<', $submission->git_timestamp)
            ->count();
    }

    /**
     * Gets the order number of the given submission in charon for student. So if this is the 3rd submission
     * this will return 3.
     *
     * @param Submission $submission
     * @param int $studentId
     *
     * @return int
     */
    public function getSubmissionCharonOrderNumber(Submission $submission, int $studentId)
    {
        return $this->buildForUser($studentId)
            ->where('charon_submission.git_timestamp', '<', $submission->git_timestamp)
            ->where('charon_submission.charon_id', $submission->charon_id)
            ->count();
    }

    /**
     * @param $charonId
     * @param $gradeTypeCode
     *
     * @return Result[]|Collection
     */
    public function findResultsByCharonAndGradeType($charonId, $gradeTypeCode)
    {
        return Result::whereHas('submission', function ($query) use ($charonId, $gradeTypeCode) {
            $query->where('charon_id', $charonId);
        })
            ->where('grade_type_code', $gradeTypeCode)
            ->get();
    }

    /**
     * Find the latest submissions for the course with the given id.
     *
     * @param int $courseId
     *
     * @return Collection|Charon[]
     */
    public function findLatestSubmissions(int $courseId)
    {
        /** @var Collection|Charon[] $charons */
        $charons = Charon::where('course', $courseId)->get();

        $charonIds = $charons->pluck('id');

        return Submission::select(['id', 'charon_id', 'user_id', 'created_at'])
            ->whereIn('charon_id', $charonIds)
            ->with([
                'users' => function ($query) {
                    $query->select(['id', 'firstname', 'lastname']);
                },
                'user' => function ($query) {
                    $query->select(['id', 'firstname', 'lastname']);
                },
                'charon' => function ($query) {
                    $query->select(['id', 'name']);
                },
                'results' => function ($query) {
                    $query->select(['id', 'submission_id', 'calculated_result', 'grade_type_code']);
                    $query->orderBy('grade_type_code');
                },
            ])
            ->latest()
            ->simplePaginate(10);
    }

    /**
     * Provides overview stats for popup dashboard.
     *
     * Currently only submission authors are taken into account.
     *
     * @param $courseId
     * @return mixed
     */
    public function findSubmissionCounts(int $courseId)
    {
        $prefix = $this->moodleConfig->prefix;

        return DB::select("
        SELECT    
          c.project_folder, 
          c.id                                               AS charon_id, 
          Count(DISTINCT cs.user_id)                         AS diff_users, 
          Count(DISTINCT cs.id)                              AS tot_subs, 
          Count(DISTINCT cs.id) / Count(DISTINCT cs.user_id) AS subs_per_user , 
          ( 
                     SELECT     Avg(gg.finalgrade) 
                     FROM       " . $prefix . "grade_grades gg 
                     INNER JOIN " . $prefix . "grade_items gi 
                     ON         gg.itemid = gi.id 
                     WHERE      gi.courseid = c.course 
                     AND        gi.itemtype = 'category' 
                     AND        gi.iteminstance = c.category_id
          ) AS avg_defended_grade,
          ( 
                     SELECT     Count(DISTINCT gg.userid) 
                     FROM       " . $prefix . "grade_grades gg 
                     INNER JOIN " . $prefix . "grade_items gi 
                     ON         gg.itemid = gi.id 
                     WHERE      gi.courseid = c.course 
                     AND        gi.itemtype = 'category' 
                     AND        gi.iteminstance = c.category_id
                     AND        gg.finalgrade > 0
          ) AS defended_amount,
          (
                     SELECT     Sum(gg.finalgrade) / Count(DISTINCT gg.userid)
                     FROM       " . $prefix . "charon_grademap gm
                     INNER JOIN " . $prefix . "grade_grades gg
                     ON         gg.itemid = gm.grade_item_id
                     WHERE      c.id = gm.charon_id
                     AND        gg.finalgrade IS NOT NULL
                     AND        gm.grade_type_code < 100
          ) AS avg_raw_grade,
          Count(DISTINCT helper.userid) as successful_tests_amount
        FROM      " . $prefix . "charon c 
        LEFT JOIN " . $prefix . "charon_submission cs 
        ON        c.id = cs.charon_id 
        LEFT JOIN (
                     SELECT    gm.charon_id, gg.userid
                     FROM      " . $prefix . "charon_grademap gm
                     LEFT JOIN " . $prefix . "grade_grades gg
                     ON        gg.itemid = gm.grade_item_id
                     WHERE     gg.finalgrade IS NOT NULL
                     AND       gm.grade_type_code < 100
                     GROUP BY  gm.charon_id, gg.userid
                     HAVING    Sum(gg.finalgrade) > Sum(gg.rawgrademax) / 2
                  ) AS helper
        ON        c.id = helper.charon_id  
        WHERE     c.course = ? 
        GROUP BY  c.project_folder, 
                  c.id, 
                  c.category_id, 
                  c.course 
        ORDER BY  subs_per_user DESC
        ", [$courseId]);
    }

    /**
     * Find all Submissions for report table by given id.
     *
     * @param $courseId
     *
     * @return mixed
     */
    public function findAllSubmissionsForReport($courseId, $page, $perPage, $sortField, $sortType, $firstName, $lastName,
                                                $exerciseName, $isConfirmed, $gitTimestampForStartDate, $gitTimestampForEndDate)
    {
        // TODO: Convert to query builder?
        function escapeDoubleQuotes($string)
        {
            $return = DB::getPdo()->quote($string);
            return $return && strlen($return) >= 2 ? substr($return, 1, strlen($return) - 2) : $return;
        }

        $firstName = escapeDoubleQuotes($firstName);
        $lastName = escapeDoubleQuotes($lastName);
        $exerciseName = escapeDoubleQuotes($exerciseName);
        $isConfirmed = escapeDoubleQuotes($isConfirmed);
        $gitTimestampForStartDate = escapeDoubleQuotes($gitTimestampForStartDate);
        $gitTimestampForEndDate = escapeDoubleQuotes($gitTimestampForEndDate);
        $page = escapeDoubleQuotes($page);
        $perPage = escapeDoubleQuotes($perPage);
        $sortField = escapeDoubleQuotes($sortField);
        $sortType = escapeDoubleQuotes($sortType);

        $rows = ($page - 1) * $perPage;
        $where = '';

        if ($firstName != ' ')
            $where .= " AND us.firstname like '%$firstName%'";
        if ($lastName != ' ')
            $where .= " AND us.lastname like '%$lastName%'";
        if ($exerciseName != ' ')
            $where .= " AND ch.name like '%$exerciseName%'";
        if ($isConfirmed != ' ')
            $where .= " AND ch_su.confirmed = '$isConfirmed'";
        if ($gitTimestampForStartDate != ' ')
            $where .= " AND ch_su.git_timestamp >= '$gitTimestampForStartDate'";
        if ($gitTimestampForEndDate != ' ')
            $where .= " AND ch_su.git_timestamp <= '$gitTimestampForEndDate'";

        if ($sortField == 'exerciseName')
            $sortField = 'name';
        if ($sortField == 'isConfirmed')
            $sortField = 'confirmed';
        if ($sortField == 'gitTimestampForStartDate')
            $sortField = 'git_timestamp';
        if ($sortField == 'gitTimestampForEndDate')
            $sortField = 'git_timestamp';

        $prefix = $this->moodleConfig->prefix;

        $result = DB::select(DB::raw(
            "SELECT SQL_CALC_FOUND_ROWS ch_su.id, us.firstname, us.lastname, ch.name, GROUP_CONCAT(ch_re.calculated_result
            ORDER BY ch_re.grade_type_code SEPARATOR ' | ') AS submission_result, SUM(CASE WHEN ch_re.grade_type_code <= 100
            THEN ch_re.calculated_result ELSE 0 END) AS submission_tests_sum, ROUND(gr_gr.finalgrade, 2) AS finalgrade,
            ch_su.confirmed, ch_su.git_timestamp
	            FROM " . $prefix . "charon_submission ch_su
		            INNER JOIN " . $prefix . "user us ON us.id = ch_su.user_id
		            INNER JOIN " . $prefix . "charon ch ON ch.id = ch_su.charon_id AND ch.course = '$courseId'
		            INNER JOIN " . $prefix . "charon_result ch_re ON ch_re.submission_id = ch_su.id
		            LEFT JOIN " . $prefix . "grade_items gr_it ON gr_it.iteminstance = ch.category_id AND gr_it.itemtype = 'category'
		            LEFT JOIN " . $prefix . "grade_grades gr_gr ON gr_gr.userid = ch_su.user_id
		        WHERE gr_gr.itemid = gr_it.id $where
	        GROUP BY ch_su.id, us.firstname, us.lastname, ch.name, finalgrade, ch_su.confirmed, ch_su.git_timestamp
	        ORDER BY $sortField $sortType
	        LIMIT $rows, $perPage"
        ));

        $resultRows = DB::select(DB::raw(
            "SELECT FOUND_ROWS() AS totalRecords"
        ));

        return array("rows" => $result, "totalRecords" => $resultRows[0]->totalRecords);
    }

    /**
     * Build a query for submissions by user in many-to-many table
     *
     * @param int $userId
     *
     * @return Builder
     */
    private function buildForUser(int $userId) {
        return DB::table('charon_submission')->join('charon_submission_user', function ($join) use ($userId) {
            $join->on('charon_submission.id', '=', 'charon_submission_user.submission_id')
                ->where('charon_submission_user.user_id', '=', $userId);
        });
    }
}
