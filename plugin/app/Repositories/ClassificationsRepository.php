<?php

namespace TTU\Charon\Repositories;

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
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllTesterTypes()
    {
        return TesterType::all();
    }

    /**
     * Add a tester type.
     *
     * @param String $name
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function saveTesterTypes($name)
    {
        $testerType = TesterType::create([
            'name' => $name
        ]);

        $testerType->load([
            'teacher' => function ($query) {
                $query->select(['code', 'name']);
            },
        ]);

        return $testerType;
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
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllGradingMethods()
    {
        return GradingMethod::all();
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

    /**
     * Get all the plagiarism checking services that the plagiarism service
     * (julia) supports.
     *
     * @return \Illuminate\Database\Eloquent\Collection|PlagiarismService[]
     */
    public function getAllPlagiarismServices()
    {
        return PlagiarismService::all();
    }
}
