<?php

namespace TTU\Charon\Repositories;

use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;

/**
 * Class SubmissionsRepository.
 *
 * @package TTU\Charon\Repositories
 */
class SubmissionsRepository
{
    /**
     * Get submissions for the current student and charon. Also eager loads their results.
     *
     * @param  int $charonId
     * @param  int $studentId
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSubmissionsForStudentAndCharon($charonId, $studentId)
    {
        $submissions = Submission::with(['results' => function ($q) {
            $q->select('calculated_result', 'submission_id', 'grade_type_code');
        }])
            ->where('charon_id', $charonId)
            ->where('user_id', $studentId)
            ->get(['id', 'confirmed', 'created_at', 'git_hash', 'git_timestamp', 'git_commit_message']);
        return $submissions;
    }

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
}
