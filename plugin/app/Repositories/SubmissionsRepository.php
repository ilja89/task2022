<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;
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
     * @param $id
     *
     * @return Submission
     */
    public function find($id): Submission
    {
        return Submission::find($id);
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
            'git_callback_id',
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
                $query->select(['id', 'user_id', 'submission_id', 'calculated_result', 'grade_type_code', 'percentage']);
                $query->orderBy('grade_type_code');
            },
            'grader' => function ($query) {
                $query->select(['id', 'firstname', 'lastname', 'email', 'idnumber', 'username']);
            },
            'users' => function ($query) {
                $query->select(['id', 'firstname', 'lastname', 'username']);
            },
            'gitCallback' => function ($query) {
                $query->select(['id', 'repo']);
            },
        ])->where('id', $submissionId)->first($fields);
    }

    /**
     * Latest laravel has joinSub, currently using raw query
     *
     * @param int $charonId
     *
     * @return int[]
     */
    public function findLatestByCharon(int $charonId): array
    {
        $prefix = $this->moodleConfig->prefix;

        $submissions = DB::select(
            'SELECT DISTINCT cs1.id '
                . 'FROM ' . $prefix . 'charon_submission AS cs1 '
                . 'JOIN ' . $prefix . 'charon_submission_user AS csu1 ON cs1.id = csu1.submission_id '
                . 'JOIN ( '
                . '    SELECT '
                . '        csu2.user_id, '
                . '        max(cs2.created_at) AS created_at '
                . '    FROM ' . $prefix . 'charon_submission AS cs2 '
                . '    JOIN ' . $prefix . 'charon_submission_user AS csu2 ON cs2.id = csu2.submission_id '
                . '    WHERE cs2.charon_id = ? '
                . '    GROUP BY csu2.user_id '
                . ') AS latest_per_user ON csu1.user_id = latest_per_user.user_id AND cs1.created_at = latest_per_user.created_at '
                . 'WHERE cs1.charon_id = ?',
            [$charonId, $charonId]
        );

        return collect($submissions)->pluck('id')->all();
    }

    /**
     * @param int $userId
     *
     * @return Collection|Submission[]
     */
    public function findUserSubmissions(int $userId)
    {
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
     * @return array
     */
    public function paginateSubmissionsByCharonUser(Charon $charon, int $userId): array
    {
        $submissionFields = [
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

        $unitTestFields = [
            'charon_unit_test.id',
            'charon_unit_test.test_suite_id',
            'charon_unit_test.groups_depended_upon',
            'charon_unit_test.status',
            'charon_unit_test.weight',
            'charon_unit_test.print_exception_message',
            'charon_unit_test.print_stack_trace',
            'charon_unit_test.time_elapsed',
            'charon_unit_test.methods_depended_upon',
            'charon_unit_test.stack_trace',
            'charon_unit_test.name',
            'charon_unit_test.stdout',
            'charon_unit_test.exception_class',
            'charon_unit_test.exception_message',
            'charon_unit_test.stderr',

        ];

        $submissions = Submission::select($submissionFields)
            ->where('charon_id', $charon->id)
            ->with([
                'results' => function ($query) use ($charon, $userId) {
                    $query->whereIn('grade_type_code', $charon->getGradeTypeCodes())
                        ->where('user_id', $userId)
                    ->select(['id', 'user_id', 'submission_id', 'calculated_result', 'grade_type_code', 'percentage']);
                    $query->orderBy('grade_type_code');
                },
                'users' => function ($query) {
                    $query->select(['id', 'username']);
                },
                'testSuites' => function ($query) {
                    $query->select(['*']);
                },
                'unitTests' => function ($query) use ($unitTestFields) {
                    $query->select($unitTestFields);
                },
                'files' => function ($query) {
                    $query->select(['id', 'submission_id', 'path', 'contents', 'is_test']);
                },
                'reviewComments' => function ($query) {
                    $query->select(['charon_review_comment.id', 'charon_review_comment.submission_file_id']);
                },
            ])
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('id', '=', $userId);
            })
            ->orderByDesc('confirmed')
            ->latest()
            ->simplePaginate(config('app.page_size'));

        $submissions->appends(['user_id' => $userId])->links();

        return [$this->assignUnitTestsToTestSuites($submissions), config('app.page_size')];
    }

    /**
     * Finds all submissions which are confirmed for given user and Charon.
     *
     * @param int $userId
     * @param int $charonId
     *
     * @return Submission[]|Collection
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
    public function charonHasConfirmedSubmissions(int $charonId, int $userId): bool
    {
        $existing = $this->buildForUser($userId)
            ->where('charon_submission.charon_id', $charonId)
            ->where('charon_submission.confirmed', 1)
            ->select('charon_submission.id')
            ->count();

        return $existing > 0;
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
     * @param int $user_id
     * @param int $gradeTypeCode
     * @param string $stdout
     *
     * @return void
     */
    public function saveNewEmptyResult(int $submissionId, int $user_id, int $gradeTypeCode, $stdout = 'An empty result')
    {
        Result::create([
            'submission_id' => $submissionId,
            'user_id' => $user_id,
            'grade_type_code' => $gradeTypeCode,
            'percentage' => 0,
            'calculated_result' => 0,
            'stdout' => $stdout,
        ]);
    }

    /**
     * @param int $submissionId
     * @param int $userId
     * @param int $charonId
     * @param int $gradeTypeCode
     */
    public function carryPersistentResult(
        int $submissionId,
        int $userId,
        int $charonId,
        int $gradeTypeCode
    ) {
        /** @var Result $previous */
        $previous = $this->buildForUser($userId)
            ->join('charon_result', 'charon_submission.id', '=', 'charon_result.submission_id')
            ->select('charon_result.*')
            ->where('charon_submission.charon_id', $charonId)
            ->where('charon_submission.confirmed', 1)
            ->whereNotNull('charon_submission.grader_id')
            ->where('charon_result.grade_type_code', $gradeTypeCode)
            ->where('charon_result.user_id', $userId)
            ->orderBy('charon_submission.updated_at', 'desc')
            ->first();

        if (!$previous) {
            $this->saveNewEmptyResult($submissionId, $userId, $gradeTypeCode);
            return;
        }

        Result::create([
            'submission_id' => $submissionId,
            'user_id' => $userId,
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
        return Submission::join('charon', 'charon.id', 'charon_submission.charon_id')
            ->where('charon.course', $courseId)
            ->select(
                'charon_submission.id',
                'charon_submission.charon_id',
                'charon_submission.user_id',
                'charon_submission.created_at'
            )
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
                    $query->select(['id', 'user_id', 'submission_id', 'calculated_result', 'grade_type_code']);
                    $query->orderBy('grade_type_code');
                },
                'files' => function ($query) {
                    $query->select(['id', 'submission_id']);
                },
                'reviewComments' => function ($query) {
                    $query->select(['charon_review_comment.id', 'charon_review_comment.submission_file_id']);
                },
            ])
            ->latest()
            ->simplePaginate(10);
    }

    /**
     * Find the latest submissions for the course by user.
     *
     * @param int $courseId
     * @param int $userId
     *
     * @return Collection
     */
    public function findLatestSubmissionsByUser(int $courseId, int $userId)
    {
        return DB::table('charon_submission as cs')
            ->join('charon as c', 'cs.charon_id', '=', 'c.id')
            ->select('cs.created_at', 'cs.id', 'c.name')
            ->where('cs.user_id', $userId)
            ->where('c.course', $courseId)
            ->latest()
            ->take(10)
            ->get();
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
        $sortField = $this->getSortField(escapeDoubleQuotes($sortField));
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

        $prefix = $this->moodleConfig->prefix;

        $result = DB::select(DB::raw(
            "SELECT 
                SQL_CALC_FOUND_ROWS ch_su.id,
                us.firstname,
                us.lastname,
                ch.name,
                GROUP_CONCAT(ch_re.calculated_result ORDER BY ch_re.grade_type_code SEPARATOR ' | ') AS submission_result,
                SUM(CASE WHEN ch_re.grade_type_code <= 100 THEN ch_re.calculated_result ELSE 0 END) AS submission_tests_sum,
                ROUND(gr_gr.finalgrade, 2) AS finalgrade,
                ch_su.confirmed,
                ch_su.git_timestamp
            FROM " . $prefix . "charon_submission ch_su
                INNER JOIN " . $prefix . "user us ON us.id = ch_su.user_id
                INNER JOIN " . $prefix . "charon ch ON ch.id = ch_su.charon_id AND ch.course = '$courseId'
                INNER JOIN " . $prefix . "charon_result ch_re ON ch_re.submission_id = ch_su.id AND ch_re.user_id = us.id
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
     * Query for all users associated to the submission
     *
     * @param int $submissionId
     *
     * @return Collection
     */
    public function findAllUsersAssociated(int $submissionId): Collection
    {
        return DB::table('user')
            ->join('charon_submission_user', 'charon_submission_user.user_id', '=', 'user.id')
            ->where('charon_submission_user.submission_id', $submissionId)
            ->select('*')
            ->get();
    }

    /**
     * Find submission file by its id.
     *
     * @param int $fileId
     * @return SubmissionFile
     */
    public function getSubmissionFileById(int $fileId): SubmissionFile
    {
        return SubmissionFile::find($fileId);
    }

    /**
     * Build a query for submissions by user in many-to-many table
     *
     * @param int $userId
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function buildForUser(int $userId)
    {
        return DB::table('charon_submission')->join('charon_submission_user', function ($join) use ($userId) {
            $join->on('charon_submission.id', '=', 'charon_submission_user.submission_id')
                ->where('charon_submission_user.user_id', '=', $userId);
        });
    }

    /**
     * Filter allowed fields to sort by.
     *
     * Both gitTimestampForStartDate and gitTimestampForEndDate default to git_timestamp
     *
     * @param string $sortField
     *
     * @return string
     */
    private function getSortField(string $sortField): string
    {
        switch ($sortField) {
            case 'exerciseName':
                return 'name';
            case 'submissionTestsSum':
                return 'submission_tests_sum';
            case 'submissionTotal':
                return 'finalgrade';
            case 'isConfirmed':
                return 'confirmed';
            case 'firstName':
                return 'firstName';
            case 'lastName':
                return 'lastName';
            default:
                return 'git_timestamp';
        }
    }

    /**
     * @param $submissions
     * @return mixed
     */
    public function assignUnitTestsToTestSuites($submissions)
    {
        foreach ($submissions as $submission) {
            foreach ($submission->testSuites as $testSuite) {
                $unit_tests = [];
                foreach ($submission->unitTests as $unitTest) {
                    if ($unitTest->test_suite_id === $testSuite->id) {
                        array_push($unit_tests, $unitTest);
                    }
                }
                $testSuite->unit_tests = $unit_tests;
            }
        }
        foreach ($submissions as $submission) {
            unset($submission->unitTests);
        }
        return $submissions;
    }

    /**
     * @param $submissionId
     * @return TestSuite[]
     */
    public function getTestSuites($submissionId)
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
     * Get all submissions for given user in given course
     *
     * @param int $courseId
     * @param int $userId
     *
     * @return int
     */
    public function countAllUserSubmissions(int $courseId, int $userId)
    {
        return DB::table('charon_submission as cs')
            ->join('charon as c', 'cs.charon_id', '=', 'c.id')
            ->where('c.course', $courseId)
            ->where('cs.user_id', $userId)
            ->count();
    }

    /**
     * Get number of charons with at least 1 submission in given course
     *
     * @param int $courseId
     * @param int $userId
     *
     * @return mixed
     */

    public function getNumberOfCharonsWithSubmissions(int $courseId, int $userId)
    {
        return DB::table('charon_submission as cs')
            ->join('charon as c', 'cs.charon_id', '=', 'c.id')
            ->where('c.course', $courseId)
            ->where('cs.user_id', $userId)
            ->distinct('c.id')
            ->count('c.id');
    }
}
