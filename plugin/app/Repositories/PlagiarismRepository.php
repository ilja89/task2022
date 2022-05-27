<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Class PlagiarismRepository.
 *
 * @package TTU\Charon\Repositories
 */
class PlagiarismRepository
{

    /**
     * Get all Charon assignment Ids for the given course.
     *
     * @param int $courseId
     * @return array
     */
    public function getAllPlagiarismAssignmentIds(int $courseId): array
    {
        return DB::table('charon')
            ->where('course', $courseId)
            ->whereNotNull('plagiarism_assignment_id')
            ->pluck('plagiarism_assignment_id')
            ->toArray();
    }
}
