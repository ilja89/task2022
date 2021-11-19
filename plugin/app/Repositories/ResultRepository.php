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
     * @param $charonId
     * @param $gradeTypeCode
     *
     * @return Result[]|Collection
     */
    public function findResultsByCharonAndGradeType($charonId, $gradeTypeCode)
    {
        return Result::whereHas('submission', function ($query) use ($charonId, $gradeTypeCode) {
            $query->where('charon_id', $charonId);
        })
            ->where('grade_type_code', $gradeTypeCode)
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Finds the result previous to the given one that has the same grade type, user, and charon.
     *
     * @param Result $result
     *
     * @return ?Result
     */
    public function findPreviousResultForUser(Result $result): ?Result
    {
        return Result::join("charon_submission", "charon_result.submission_id", "charon_submission.id")
            ->where("charon_result.grade_type_code", $result->grade_type_code)
            ->where("charon_submission.charon_id", $result->submission->charon_id)
            ->where("charon_result.user_id", $result->user_id)
            ->where("charon_result.id", "<", $result->id)
            ->select("charon_result.id", "charon_result.calculated_result", "charon_result.percentage")
            ->orderBy("charon_result.id", "desc")
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
