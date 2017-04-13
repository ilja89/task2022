<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Zeizig\Moodle\Models\CourseModule::class, function (Faker\Generator $faker) {
    return [
        'instance' => function () {
            return factory(\TTU\Charon\Models\Charon::class)->create()->id;
        },
        'module'   => app('Zeizig\Moodle\Services\ModuleService')->getModuleId(),
        'course'   => function (array $courseModule) {
            return $courseModule['instance'];
        }
    ];
});

$factory->define(Zeizig\Moodle\Models\Module::class, function (Faker\Generator $faker) {
    return [
        'name' => config('moodle.plugin_slug'),
    ];
});

$factory->define(Zeizig\Moodle\Models\Course::class, function (Faker\Generator $faker) {
    return [
        'fullname'  => $faker->sentence,
        'shortname' => $faker->word
    ];
});
