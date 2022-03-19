<?php

namespace TTU\Charon\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\GitlabLocationType;
use TTU\Charon\Models\GradeNamePrefix;
use TTU\Charon\Models\GradingMethod;
use TTU\Charon\Models\PlagiarismLangType;
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

    /**
     * Get all plagiarism language types.
     *
     * @return static[]
     */
    public function getAllPlagiarismLangTypes(): array
    {
        return [
            (object)['code' => 'a8086', 'name' => '8086 Assembly'],
            (object)['code' => 'ada', 'name' => 'Ada'],
            (object)['code' => 'ascii', 'name' => 'ASCII'],
            (object)['code' => 'c', 'name' => 'C'],
            (object)['code' => 'cc', 'name' => 'C++'],
            (object)['code' => 'csharp', 'name' => 'C#'],
            (object)['code' => 'fortran', 'name' => 'Fortran'],
            (object)['code' => 'haskell', 'name' => 'Haskell'],
            (object)['code' => 'java', 'name' => 'Java'],
            (object)['code' => 'javascript', 'name' => 'JavaScript'],
            (object)['code' => 'lisp', 'name' => 'Lisp'],
            (object)['code' => 'matlab', 'name' => 'MATLAB'],
            (object)['code' => 'mips', 'name' => 'MIPS Assembly'],
            (object)['code' => 'ml', 'name' => 'ML'],
            (object)['code' => 'modula2', 'name' => 'Modula2'],
            (object)['code' => 'pascal', 'name' => 'Pascal'],
            (object)['code' => 'perl', 'name' => 'Perl'],
            (object)['code' => 'plsql', 'name' => 'PL/SQL'],
            (object)['code' => 'prolog', 'name' => 'Prolog'],
            (object)['code' => 'python', 'name' => 'Python'],
            (object)['code' => 'scheme', 'name' => 'Scheme'],
            (object)['code' => 'spice', 'name' => 'Spice'],
            (object)['code' => 'vb', 'name' => 'Visual Basic'],
            (object)['code' => 'vhdl', 'name' => 'VHDL'],
        ];
    }

    /**
     * Get all gitlab location types.
     *
     * @return static[]
     */
    public function getAllGitlabLocationTypes(): array
    {
        return [
            ['name' => 'Projects',
            'code' => 'Projects'],

            ['name' => 'Shared Projects',
            'code' => 'Shared Projects']
        ];
    }
}
