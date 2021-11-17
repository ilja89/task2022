<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Collection;
use TTU\Charon\Models\Result;

class ResultRepository
{
    /**
     * Assumes submission and grade type to be provided
     *
     * @param array $fields
     */
    public function saveIfGrademapPresent($fields = [])
    {
        if (!isset($fields['submission_id']) || !isset($fields['grade_type_code'])) {
            return;
        }

        $result = new Result($fields);

        if ($result->getGrademap() != null) {
            $result->save();
        }
    }

    /**
     * Finds a result by its 'grade_type_code' with the highest
     * 'calculated_result' of a given user for given charon.
     *
     * @param int $gradeTypeCode
     * @param int $charonId
     * @param int $userId
     *
     * @return ?Result
     */
    public function findWithHighestCalculatedResultForUser(int $gradeTypeCode, int $charonId, int $userId): ?Result
    {
        $query = "CAST(calculated_result AS DECIMAL(3, 2)) DESC";

        return Result::join("charon_submission", "charon_result.submission_id", "charon_submission.id")
            ->where("charon_result.grade_type_code", $gradeTypeCode)
            ->where("charon_submission.charon_id", $charonId)
            ->where("charon_result.user_id", $userId)
            ->select("charon_result.calculated_result", "charon_result.percentage")
            ->orderByRaw($query)
            ->first();
    }

    /**
     * Reset all 'calculated_result's of results with given ids.
     *
     * @param Collection|int[] $resultIds
     */
    public function resetResultsCalculatedResults(array $resultIds)
    {
        Result::whereIn("id", $resultIds)
            ->update(["calculated_result" => 0]);
    }
}
