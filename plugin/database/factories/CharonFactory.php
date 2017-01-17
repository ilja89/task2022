<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(TTU\Charon\Models\Charon::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->name,
        'description' => $faker->paragraph,
        'project_folder' => $faker->word,
        'extra' => $faker->word,
        'tester_type_code' => $faker->randomElement([1, 2, 3]),
        'grading_method_code' => $faker->randomElement([1, 2])
    ];
});
