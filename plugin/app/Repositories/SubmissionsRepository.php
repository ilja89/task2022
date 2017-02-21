<?php

namespace TTU\Charon\Repositories;

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
            ->get(['id', 'confirmed', 'created_at', 'git_hash', 'git_timestamp', 'mail']);
        return $submissions;
    }
}
