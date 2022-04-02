<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\PlagiarismCheck;

/**
 * Class PlagiarismRepository.
 *
 * @package TTU\Charon\Repositories
 */
class PlagiarismRepository
{
    /**
     * Save the given plagiarism check and return it.
     *
     * @param int $charonId
     * @param int $userId
     * @param string $status
     *
     * @return PlagiarismCheck
     */
    public function addPlagiarismCheck(int $charonId, int $userId, string $status): PlagiarismCheck
    {
        return PlagiarismCheck::create([
            'charon_id' => $charonId,
            'user_id' => $userId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'status' => $status,
        ]);
    }

    /**
     * Save the given plagiarism check and return it.
     *
     * @param int $courseId
     * @return array
     */
    public function getChecksByCourseId(int $courseId): array
    {
        return DB::table('charon_plagiarism_check')
            ->join('charon', 'charon_id', '=', 'charon.id')
            ->join('user', 'user_id', '=', 'user.id')
            ->select('charon_plagiarism_check.*', 'charon.name', 'user.firstname', 'user.lastname')
            ->where('charon.course', $courseId)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Fetch the plagiarism assignment id from a charon object that has the given id
     * @param int $charonId
     * @return int|null
     */
    public function getAssignmentIdByCharonId(int $charonId): ?int
    {
        return DB::table('charon')
            ->where('id', $charonId)
            ->value('plagiarism_assignment_id');
    }
}
