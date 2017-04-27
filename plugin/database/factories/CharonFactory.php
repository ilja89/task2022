<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(TTU\Charon\Models\Charon::class, function (Faker\Generator $faker) {

    $courseGradeItem = factory(\Zeizig\Moodle\Models\GradeItem::class, 'course_grade_item')->create();

    return [
        'name' => $faker->name,
        'description' => $faker->paragraph,
        'project_folder' => $faker->word,
        'extra' => $faker->word,
        'tester_type_code' => $faker->randomElement([1, 2, 3]),
        'grading_method_code' => $faker->randomElement([1, 2]),
        'course' => $courseGradeItem->courseid,
        'timemodified' => $faker->unixTime,
        'category_id' => function () use ($courseGradeItem) {
            $gradeItem = factory(\Zeizig\Moodle\Models\GradeItem::class, 'grade_item_with_category')->create([
                'courseid' => $courseGradeItem->courseid,
            ]);
            return \Zeizig\Moodle\Models\GradeCategory::find($gradeItem->iteminstance)->id;
        },
    ];
});

$factory->define(TTU\Charon\Models\CourseSettings::class, function (Faker\Generator $faker) {

    return [
        'course_id' => function () {
            return factory(\Zeizig\Moodle\Models\Course::class)->create()->id;
        },
        'unittests_git' => $faker->word,
        'tester_type_code' => $faker->randomElement([1, 2, 3, 4]),
    ];
});
