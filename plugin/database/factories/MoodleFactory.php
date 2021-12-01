<?php

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use TTU\Charon\Models\Charon;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\CourseModule;
use Zeizig\Moodle\Models\GradeCategory;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Models\Group;
use Zeizig\Moodle\Models\Grouping;
use Zeizig\Moodle\Models\Module;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\ModuleService;

/** @var Factory $factory */
$factory->define(CourseModule::class, function (Generator $faker) {
    return [
        'instance' => function () {
            return factory(Charon::class)->states('with_new_course')->create();
        },
        'module' => app(ModuleService::class)->getModuleId(),
        'course' => function (array $courseModule) {
            return Charon::find($courseModule['instance'])->course;
        },
        'section' => 1,
        'added' => Carbon::now()->timestamp,
    ];
});

$factory->define(Module::class, function (Generator $faker) {
    return [
        'name' => config('moodle.plugin_slug'),
    ];
});

$factory->define(Course::class, function (Generator $faker) {
    return [
        'fullname' => $faker->sentence,
        'shortname' => $faker->word,
        'category' => 1,
        'sortorder' => 10001,
        'summary' => $faker->paragraph,
        'summaryformat' => 1,
        'timecreated' => Carbon::now()->timestamp,
        'timemodified' => Carbon::now()->timestamp,
        'startdate' => Carbon::now()->timestamp,
        'enddate' => Carbon::now()->addDays(2)->timestamp,
    ];
});

$factory->define(GradeCategory::class, function (Generator $faker) {
    return [
        'courseid' => function () {
            return factory(Course::class)->create()->id;
        },
        'path' => '',
        'parent' => function (array $gradeCategory) {
            return factory(GradeCategory::class)->create([
                'courseid' => $gradeCategory['courseid'],
                'parent'   => null,
            ])->id;
        },
        'depth' => function (array $gradeCategory) {
            return $gradeCategory['parent'] === null ? 1 : (GradeCategory::find($gradeCategory['parent'])->depth + 1);
        },
        'fullname' => $faker->sentence,
        'aggregation' => 13,
        'keephigh' => 0,
        'droplow' => 0,
        'aggregateonlygraded' => function (array $gradeCategory) {
            return $gradeCategory['parent'] === null ? 1 : 0;
        },
        'aggregateoutcomes' => 0,
        'timecreated' => Carbon::now()->timestamp,
        'timemodified' => Carbon::now()->timestamp,
        'hidden' => 0,
    ];
});

$factory->define(GradeItem::class, function (Generator $faker) {
    return [
        'courseid' => function () {
            return factory(Course::class)->create()->id;
        },
        'categoryid' => null,
        'itemname' => null,
        'itemtype' => 'course',
        'itemmodule' => null,
        'iteminstance' => function (array $gradeItem) {
            return factory(GradeCategory::class)->create([
                'courseid' => $gradeItem['courseid'],
                'parent' => null,
                'fullname' => '?',
            ])->id;
        },
        'itemnumber' => null,
    ];
}, 'course_grade_item');

$factory->define(GradeItem::class, function (Generator $faker) {
    return [
        'courseid' => function () {
            return factory(Course::class)->create()->id;
        },
        'categoryid' => null,
        'itemname' => null,
        'itemtype' => 'category',
        'itemmodule' => null,
        'iteminstance' => function (array $gradeItem) {
            return factory(GradeCategory::class)->create([
                'courseid' => $gradeItem['courseid'],
            ])->id;
        },
        'grademax' => $faker->randomFloat(2, 0, 10)
    ];
}, 'grade_item_with_category');

$factory->define(Grouping::class, function (Generator $faker) {
    return [
        'courseid' => 0,
        'name' => $faker->word,
        'idnumber' => $faker->randomDigitNotNull,
        'description' => $faker->sentence,
        'timecreated' => Carbon::now()->timestamp,
        'timemodified' => Carbon::now()->timestamp
    ];
});

$factory->define(User::class, function (Generator $faker) {
    return [
        'auth' => $faker->word,
        'username' => $faker->firstName . $faker->randomAscii,
        'firstname' => $faker->word,
        'lastname' => $faker->word,
        'confirmed' => 1,
        'email' => $faker->email
    ];
});

$factory->define(Group::class, function (Generator $faker) {
    return [
        'courseid' => $faker->randomDigitNotNull,
        'name' => $faker->word,
        'description' => $faker->sentence,
    ];
});
