<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    /**
     * Find submission by its ID. Leave out stdout, stderr because it might be too big.
     *
     * @param  int  $submissionId
     * @param  int[]  $gradeTypeCodes
     *
     * @return Submission
     */
    public function findByIdWithoutOutputs($submissionId, $gradeTypeCodes)
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
        ];

        /** @var Submission $submission */
        $submission = Submission::with([
            'results' => function ($query) use ($gradeTypeCodes) {
                // Only select results which have a corresponding grademap
                $query->whereIn('grade_type_code', $gradeTypeCodes);
                $query->select(['id', 'submission_id', 'calculated_result', 'grade_type_code']);
                $query->orderBy('grade_type_code');
            },
            'grader' => function ($query) {
                $query->select(['id', 'firstname', 'lastname', 'email', 'idnumber']);
            },
        ])
            ->where('id', $submissionId)
            ->first($fields);

        return $submission;
    }

    /**
     * Find the submission's outputs. Returns them in an array like so:
     *      [ 'submission' => [ 'stdout' => '...', 'stderr' => '...' ],
     *        'results' => [ 1 => ['stdout' => '...', 'stderr' => '...' ],
     *                       2 => ['stdout' => '...', 'stderr' => '...' ] ] ]
     *
     * @param  Submission $submission
     *
     * @return array
     */
    public function findSubmissionOutputs(Submission $submission)
    {
        $outputs                         = [];
        $outputs['submission']['stdout'] = $submission->stdout;
        $outputs['submission']['stderr'] = $submission->stderr;
        foreach ($submission->results as $result) {
            if ($result->getGrademap() === null) {
                // If has no grademap - result exists but no grademap
                continue;
            }

            $outputs['results'][$result->id]['stdout'] = $result->stdout;
            $outputs['results'][$result->id]['stderr'] = $result->stderr;
        }

        return $outputs;
    }

    /**
     * Find submissions by charon and user. Also paginates this info by 5.
     *
     * @param  Charon  $charon
     * @param  int  $userId
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function paginateSubmissionsByCharonUser(Charon $charon, $userId)
    {
        $fields = [
            'id',
            'charon_id',
            'confirmed',
            'created_at',
            'git_hash',
            'git_timestamp',
            'git_commit_message',
            'user_id',
            'mail',
        ];
        $submissions = Submission::with([
            // Only select results which have a corresponding grademap
            'results' => function ($query) use ($charon) {
                $query->whereIn('grade_type_code', $charon->getGradeTypeCodes());
                $query->select(['id', 'submission_id', 'calculated_result', 'grade_type_code']);
                $query->orderBy('grade_type_code');
            },
        ])
             ->where('charon_id', $charon->id)
             ->where('user_id', $userId)
             ->orderBy('created_at', 'desc')
             ->orderBy('git_timestamp', 'desc')
             ->select($fields)
             ->simplePaginate(5);
        $submissions->appends(['user_id' => $userId])->links();
        foreach($submissions as $submission) {
            $submission['test_suites'] = $this->getTestSuites($submission->id);
        }
        return $submissions;
    }

    /**
     * @param $submissionId
     * @return TestSuite[]
     */
    private function getTestSuites($submissionId) {
        $testSuites = \DB::table('charon_test_suite')
            ->where('submission_id', $submissionId)
            ->select('*')
            ->get();
        for($i = 0; $i < count($testSuites); $i++) {
            $testSuites[$i]->unit_tests = $this->getUnitTestsResults($testSuites[$i]->id);
        }
        return $testSuites;
    }

    /**
     * @param $testSuiteId
     * @return UnitTest[]
     */
    private function getUnitTestsResults($testSuiteId) {
        return \DB::table('charon_unit_test')
            ->where('test_suite_id', $testSuiteId)
            ->select('*')
            ->get();
    }

    /**
     * Finds all submissions which are confirmed for given user and Charon.
     *
     * @param  int  $userId
     * @param  int  $charonId
     *
     * @return Submission[]
     */
    public function findConfirmedSubmissionsForUserAndCharon($userId, $charonId)
    {
        return Submission::where('charon_id', $charonId)
            ->where('user_id', $userId)
            ->where('confirmed', 1)
            ->get();
    }

    /**
     * Finds all confirmed submissions for given user.
     *
     * @param  int  $userId
     *
     * @return array
     */
    public function findConfirmedSubmissionsForUser($courseId, $userId)
    {
        global $CFG;
        $prefix = $CFG->prefix;

        $result = DB::select('SELECT ch.id, ch.name, gr_gr.finalgrade
	                            FROM '.$prefix.'charon ch
	                            LEFT JOIN '.$prefix.'grade_items gr_it ON gr_it.iteminstance = ch.category_id AND gr_it.itemtype = "category"
	                            LEFT JOIN '.$prefix.'grade_grades gr_gr ON gr_gr.itemid = gr_it.id
	                        WHERE ch.course = ? AND gr_gr.userid = ?', [$courseId, $userId]
        );
        return $result;
    }

    /**
     * Finds all course submissions and calculates each Charon average.
     *
     * @param $courseId
     *
     * @return array
     */
    public function findBestAverageCourseSubmissions($courseId)
    {
        global $CFG;
        $prefix = $CFG->prefix;

        $result = DB::select(
            'SELECT ch.id, ch.name, gr_it.grademax, AVG(gr_gr.finalgrade) AS course_average_finalgrade
	            FROM '.$prefix.'charon ch
	            LEFT JOIN '.$prefix.'grade_items gr_it ON gr_it.iteminstance = ch.category_id AND gr_it.itemtype = "category"
	            LEFT JOIN '.$prefix.'grade_grades gr_gr ON gr_gr.itemid = gr_it.id
	            WHERE ch.course = ?
	        GROUP BY ch.id, ch.name, gr_it.grademax', [$courseId]
        );

        return $result;
    }

    /**
     * Confirms the given submission.
     *
     * @param  Submission $submission
     * @param  int|null $graderId
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
     * @param  Submission  $submission
     *
     * @return void
     */
    public function unconfirmSubmission(Submission $submission)
    {
        $submission->confirmed = 0;
        $submission->save();
    }

    /**
     * Check if the given user has any confirmed submissions for the given
     * Charon.
     *
     * @param  int  $charonId
     * @param  int  $userId
     *
     * @return bool
     */
    public function charonHasConfirmedSubmissions($charonId, $userId)
    {
        /** @var Submission $submission */
        $submission = Submission::where('charon_id', $charonId)
                                ->where('confirmed', 1)
                                ->where('user_id', $userId)
                                ->get();

        return ! $submission->isEmpty();
    }

    /**
     * Saves the given result.
     *
     * @param  Result  $result
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
     * @param  int  $submissionId
     * @param  int  $gradeTypeCode
     * @param  string  $stdout
     *
     * @return void
     */
    public function saveNewEmptyResult($submissionId, $gradeTypeCode, $stdout = null)
    {
        if ($stdout === null) {
            $stdout = 'An empty result';
        }

        $result = new Result([
            'submission_id'     => $submissionId,
            'grade_type_code'   => $gradeTypeCode,
            'percentage'        => 0,
            'calculated_result' => 0,
            'stdout'            => $stdout,
        ]);
        $result->save();
    }

    /**
     * Gets the order number of the given submission. So if this is the 3rd submission
     * this will return 3.
     *
     * @param Submission $submission
     *
     * @return int
     */
    public function getSubmissionOrderNumber(Submission $submission)
    {
        return \DB::table('charon_submission')
                 ->where('user_id', $submission->user_id)
                 ->where('git_timestamp', '<', $submission->git_timestamp)
                 ->count();
    }

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
    public function findLatestSubmissions($courseId)
    {
        /** @var Collection|Charon[] $charons */
        $charons = Charon::where('course', $courseId)->get();

        $charonIds = $charons->pluck('id');

        $submissions = Submission::select(['id', 'charon_id', 'user_id', 'created_at'])
            ->whereIn('charon_id', $charonIds)
            ->with([
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

        return $submissions;
    }

    public function findSubmissionCounts($courseId)
    {

        global $CFG;
        $prefix = $CFG->prefix;

        // TODO: Convert to query builder?
        $result = DB::select(
            'select     
	c.project_folder,     
	c.id as charon_id,
	count(distinct cs.user_id) as diff_users,     
	count(distinct cs.id) as tot_subs,     
	count(distinct cs.id) / count(distinct cs.user_id) as subs_per_user ,     
	(
	select          
		avg(gg.finalgrade)     
	from        
		'.$prefix.'grade_grades gg        
	inner join 
		'.$prefix.'grade_items gi 
	on 
		gg.itemid = gi.id        
	where gi.courseid = c.course        
	and gi.itemtype = \'category\'        
	and gi.iteminstance = c.category_id
	) as avg_grade
from '.$prefix.'charon c     
left join 
	'.$prefix.'charon_submission cs on c.id = cs.charon_id     
where c.course = ?
group by 
	c.project_folder, c.category_id, c.course
order by subs_per_user desc',
            [$courseId]
        );

        return $result;
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
            return $return && strlen($return) >= 2 ? substr($return, 1, strlen($return)-2) : $return;
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

        if($sortField == 'exerciseName')
            $sortField = 'name';
        if($sortField == 'isConfirmed')
            $sortField = 'confirmed';
        if($sortField == 'gitTimestampForStartDate')
            $sortField = 'git_timestamp';
        if($sortField == 'gitTimestampForEndDate')
            $sortField = 'git_timestamp';

        global $CFG;
        $prefix = $CFG->prefix;
        
        $result = DB::select(DB::raw(
            "SELECT SQL_CALC_FOUND_ROWS ch_su.id, us.firstname, us.lastname, ch.name, GROUP_CONCAT(ch_re.calculated_result
            ORDER BY ch_re.grade_type_code SEPARATOR ' | ') AS submission_result, SUM(CASE WHEN ch_re.grade_type_code <= 100
            THEN ch_re.calculated_result ELSE 0 END) AS submission_tests_sum, ROUND(gr_gr.finalgrade, 2) AS finalgrade,
            ch_su.confirmed, ch_su.git_timestamp
	            FROM ".$prefix."charon_submission ch_su
		            INNER JOIN ".$prefix."user us ON us.id = ch_su.user_id
		            INNER JOIN ".$prefix."charon ch ON ch.id = ch_su.charon_id AND ch.course = '$courseId'
		            INNER JOIN ".$prefix."charon_result ch_re ON ch_re.submission_id = ch_su.id
		            LEFT JOIN ".$prefix."grade_items gr_it ON gr_it.iteminstance = ch.category_id AND gr_it.itemtype = 'category'
		            LEFT JOIN ".$prefix."grade_grades gr_gr ON gr_gr.userid = ch_su.user_id
		        WHERE gr_gr.itemid = gr_it.id $where
	        GROUP BY ch_su.id, us.firstname, us.lastname, ch.name, finalgrade, ch_su.confirmed, ch_su.git_timestamp
	        ORDER BY $sortField $sortType
	        LIMIT $rows, $perPage"
        ));

        $resultRows = DB::select(DB::raw(
            "SELECT FOUND_ROWS() AS totalRecords"
        ));

        return array("rows"=>$result, "totalRecords"=>$resultRows[0]->totalRecords);
    }
}
