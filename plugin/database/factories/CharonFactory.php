<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(TTU\Charon\Models\Charon::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->name,
        'description' => $faker->paragraph,
        'project_folder' => $faker->word,
        'extra' => $faker->word,
        'tester_type_code' => $faker->randomElement([1, 2, 3]),
        'grading_method_code' => $faker->randomElement([1, 2]),
        'course' => function () {
            return factory(Zeizig\Moodle\Models\Course::class)->create()->id;
        },
        'timemodified' => $faker->unixTime
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
