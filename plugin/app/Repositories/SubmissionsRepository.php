<?php

namespace TTU\Charon\Repositories;

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
     * @param Submission $submission
     *
     * @return void
     */
    public function confirmSubmission(Submission $submission)
    {
        $submission->confirmed = 1;
        $submission->save();
    }

    /**
     * @param Submission $submission
     *
     * @return void
     */
    public function unconfirmSubmission(Submission $submission)
    {
        $submission->confirmed = 0;
        $submission->save();
    }

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
     * @param  Result  $result
     *
     * @return Result
     */
    public function saveResult(Result $result)
    {
        $result->save();
        return $result;
    }

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
}
