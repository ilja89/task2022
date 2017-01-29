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
            ['code' => 4, 'name' => 'prolog'],
        ],
        'grade_type' => [
            ['code' => 1, 'name' => 'Tests_1'],
            ['code' => 2, 'name' => 'Tests_2'],
            ['code' => 3, 'name' => 'Tests_3'],
            ['code' => 4, 'name' => 'Tests_4'],
            ['code' => 101, 'name' => 'Style_1'],
            ['code' => 102, 'name' => 'Style_2'],
            ['code' => 1001, 'name' => 'Custom_1'],
            ['code' => 1002, 'name' => 'Custom_2'],
            ['code' => 1003, 'name' => 'Custom_3']
        ],
        'grading_method' => [
            ['code' => 1, 'name' => 'prefer_best'],
            ['code' => 2, 'name' => 'prefer_last']
        ],
        'grade_name_prefix' => [
            ['code' => 1, 'name' => 'project_folder_name'],
            ['code' => 2, 'name' => 'task_name'],
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
