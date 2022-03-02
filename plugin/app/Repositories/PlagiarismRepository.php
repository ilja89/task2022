<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
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
}
