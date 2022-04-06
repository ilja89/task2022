<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubmissionTypeSeeder extends Seeder
{
    /**
     * Submission types array.
     *
     * @var array
     */
    protected $submissionTypes = [
        ['code' => 1, 'name' => 'taltech gitlab'],
        ['code' => 2, 'name' => 'inline'],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->submissionTypes as $type) {
            DB::table('charon_submission_type')->insert($type);
        }
    }
}
