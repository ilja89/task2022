<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(TTU\Charon\Models\Charon::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->name,
        'description' => $faker->paragraph
    ];
});
