<?php

use Illuminate\Database\Seeder;

class ClassificationsSeeder extends Seeder
{
    /**
     * Classifications array.
     *
     * @var array
     */
    protected $classifications = [
        'tester_type' => [
            ['code' => 1, 'name' => 'java'],
            ['code' => 2, 'name' => 'javang'],
            ['code' => 3, 'name' => 'python'],
        ],
        'grade_type' => [
            ['code' => 1, 'name' => 'Grade_1'],
            ['code' => 2, 'name' => 'Grade_2'],
            ['code' => 3, 'name' => 'Grade_3'],
            ['code' => 4, 'name' => 'Grade_4'],
            ['code' => 101, 'name' => 'Stylecheck_1'],
            ['code' => 102, 'name' => 'Stylecheck_2'],
            ['code' => 1001, 'name' => 'Custom_1'],
            ['code' => 1002, 'name' => 'Custom_2'],
            ['code' => 1003, 'name' => 'Custom_3']
        ],
        'grading_method' => [
            ['code' => 1, 'name' => 'Prefer best'],
            ['code' => 2, 'name' => 'Prefer last']
        ],
        'preset_grade_name_prefix' => [
            ['code' => 1, 'name' => 'Project folder name'],
            ['code' => 2, 'name' => 'Task name'],
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->classifications as $table => $classificationGroup) {
            foreach ($classificationGroup as $classification) {
                DB::table('charon_' . $table)->insert($classification);
            }
        }
    }
}
