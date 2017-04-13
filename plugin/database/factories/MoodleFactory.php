<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Zeizig\Moodle\Models\CourseModule::class, function (Faker\Generator $faker) {

    return [
        'instance' => $faker->randomNumber(),
        'module'   => app('Zeizig\Moodle\Services\ModuleService')->getModuleId(),
        'course'   => function () {
            return factory(Zeizig\Moodle\Models\Course::class)->create()->id;
        }
    ];
});

$factory->define(Zeizig\Moodle\Models\Module::class, function (Faker\Generator $faker) {

    return [
        'name' => 'charon'
    ];
});

$factory->define(Zeizig\Moodle\Models\Course::class, function (Faker\Generator $faker) {

    return [
        'fullname'  => $faker->sentence,
        'shortname' => $faker->word
    ];
});
