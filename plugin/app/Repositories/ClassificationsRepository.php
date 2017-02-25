<?php

namespace TTU\Charon\Repositories;

use TTU\Charon\Models\GradeNamePrefix;
use TTU\Charon\Models\GradeType;
use TTU\Charon\Models\GradingMethod;
use TTU\Charon\Models\TesterType;

/**
 * Class ClassificationsRepository.
 *
 * @package TTU\Charon\Repositories
 */
class ClassificationsRepository
{
    /**
     * Get all tester types.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllTesterTypes()
    {
        return TesterType::all();
    }

    /**
     * Get all grading methods.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllGradingMethods()
    {
        return GradingMethod::all();
    }

    /**
     * Get all grade types.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllGradeTypes()
    {
        return GradeType::all();
    }

    /**
     * Get all grade name prefixes for presets.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllGradeNamePrefixes()
    {
        return GradeNamePrefix::all();
    }
}
