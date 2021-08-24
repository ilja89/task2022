<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresetsSeeder extends Seeder
{
    protected $presets = [
        [
            'name' => 'Tests and style',
            'parent_category_id' => null,
            'course_id' => null,
            'calculation_formula' => '=[[Tests_1]] * [[Style_1]]',
            'tester_extra' => 'stylecheck',
            'grading_method_code' => 1,
            'max_result' => 1,
            'preset_grades' => [
                [
                    'grade_name_prefix_code' => 1,
                    'grade_type_code' => 1,
                    'grade_name' => ' - Tests',
                    'max_result' => 1,
                    'id_number_postfix' => '_Tests'
                ],
                [
                    'grade_name_prefix_code' => 1,
                    'grade_type_code' => 101,
                    'grade_name' => ' - Style',
                    'max_result' => 1,
                    'id_number_postfix' => '_Style'
                ]
            ]
        ],

        [
            'name' => 'Tests and defense',
            'parent_category_id' => null,
            'course_id' => null,
            'calculation_formula' => '=[[Tests_1]] * [[Custom_1]]',
            'tester_extra' => '',
            'grading_method_code' => 1,
            'max_result' => 1,
            'preset_grades' => [
                [
                    'grade_name_prefix_code' => 1,
                    'grade_type_code' => 1,
                    'grade_name' => ' - Tests',
                    'max_result' => 1,
                    'id_number_postfix' => '_Tests'
                ],
                [
                    'grade_name_prefix_code' => 1,
                    'grade_type_code' => 1001,
                    'grade_name' => ' - Defense',
                    'max_result' => 1,
                    'id_number_postfix' => '_Defense'
                ]
            ]
        ],

        [
            'name' => 'Tests, style and defense',
            'parent_category_id' => null,
            'course_id' => null,
            'calculation_formula' => '=[[Tests_1]] * [[Style_1]] * [[Custom_1]]',
            'tester_extra' => 'stylecheck',
            'grading_method_code' => 1,
            'max_result' => 1,
            'preset_grades' => [
                [
                    'grade_name_prefix_code' => 1,
                    'grade_type_code' => 1,
                    'grade_name' => ' - Tests',
                    'max_result' => 1,
                    'id_number_postfix' => '_Tests'
                ],
                [
                    'grade_name_prefix_code' => 1,
                    'grade_type_code' => 101,
                    'grade_name' => ' - Style',
                    'max_result' => 1,
                    'id_number_postfix' => '_Style'
                ],
                [
                    'grade_name_prefix_code' => 1,
                    'grade_type_code' => 1001,
                    'grade_name' => ' - Defense',
                    'max_result' => 1,
                    'id_number_postfix' => '_Defense'
                ]
            ]
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->presets as $preset) {
            $id = DB::table('charon_preset')->insertGetId([
                'name' => $preset['name'],
                'parent_category_id' => $preset['parent_category_id'],
                'course_id' => $preset['course_id'],
                'calculation_formula' => $preset['calculation_formula'],
                'tester_extra' => $preset['tester_extra'],
                'grading_method_code' => $preset['grading_method_code'],
                'max_result' => $preset['max_result'],
            ]);
            foreach ($preset['preset_grades'] as $presetGrade) {
                DB::table('charon_preset_grade')->insert(array_merge(
                    $presetGrade,
                    ['preset_id' => $id]
                ));
            }
        }
    }
}
