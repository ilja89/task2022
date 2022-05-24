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
            ->get();
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
