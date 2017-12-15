<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;

/**
 * Class SubmissionsRepository.
 *
 * @package TTU\Charon\Repositories
 */
class SubmissionsRepository
{
    /**
     * Find submission by it's ID. Leave out stdout, stderr because it might be too big.
     *
     * @param  int  $submissionId
     * @param  int[]  $gradeTypeCodes
     *
     * @return Submission
     */
    public function findByIdWithoutOutputs($submissionId, $gradeTypeCodes)
    {
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
            ->first([
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
            ]);

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
                                 ->orderBy('git_timestamp', 'desc')
                                 ->orderBy('created_at', 'desc')
                                 ->select([
                                     'id',
                                     'charon_id',
                                     'confirmed',
                                     'created_at',
                                     'git_hash',
                                     'git_timestamp',
                                     'git_commit_message',
                                     'user_id',
                                 ])
                                 ->simplePaginate(5);
        $submissions->appends(['user_id' => $userId])->links();

        return $submissions;
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
            ->with(['user' => function ($query) {
                $query->select(['id', 'firstname', 'lastname', 'idnumber']);
            }])
            ->paginate(10);

        return $submissions;
    }
}
