<?php

namespace TTU\Charon\Repositories;

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
}
