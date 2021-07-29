<?php

namespace TTU\Charon\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\GradeNamePrefix;
use TTU\Charon\Models\GradingMethod;
use TTU\Charon\Models\PlagiarismService;
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
     * @return Collection|static[]
     */
    public function getAllTesterTypes()
    {
        return TesterType::all();
    }

    public function getTesterTypeName($code)
    {
        $some = DB::table('charon_tester_type')
            ->where('code', $code)
            ->first();
        return $some;
    }

    /**
     * Add a tester type.
     *
     * @param String $name
     * @return Collection|static[]
     */
    public function saveTesterTypes($name)
    {
        $code = DB::table('charon_tester_type')
                ->max('code') + 1;

        return TesterType::create([
            'code' => $code,
            'name' => $name
        ]);
    }

    /**
     * Add a tester type.
     *
     * @param String $name
     * @return int
     */
    public function removeTesterType($name)
    {
        return DB::table('charon_tester_type')
            ->where('name', $name)
            ->delete();
    }

    /**
     * Get all grading methods.
     *
     * @return Collection|static[]
     */
    public function getAllGradingMethods()
    {
        return GradingMethod::all();
    }

    /**
     * Get all grade name prefixes for presets.
     *
     * @return Collection|static[]
     */
    public function getAllGradeNamePrefixes()
    {
        return GradeNamePrefix::all();
    }

    /**
     * Get all the plagiarism checking services that the plagiarism service
     * (julia) supports.
     *
     * @return Collection|PlagiarismService[]
     */
    public function getAllPlagiarismServices()
    {
        return PlagiarismService::all();
    }
}
