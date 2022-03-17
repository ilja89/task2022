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
            'charon_id'  => $charonId,
            'user_id' => $userId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'status'    => $status,
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
            ->where('charon.course', $courseId)
            ->select('charon_plagiarism_check.*', 'charon.name')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->toArray();
    }
}
