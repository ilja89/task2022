<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Zeizig\Moodle\Models\CourseModule::class, function (Faker\Generator $faker) {

    return [
        'instance' => function () {
            return factory(\TTU\Charon\Models\Charon::class)->create();
        },
        'module'   => app('Zeizig\Moodle\Services\ModuleService')->getModuleId(),
        'course'   => function (array $courseModule) {
            return \TTU\Charon\Models\Charon::find($courseModule['instance'])->course;
        },
        'section' => 1,
        'added' => \Carbon\Carbon::now()->timestamp,
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
        'shortname' => $faker->word,
        'category' => 1,
        'sortorder' => 10001,
        'summary' => $faker->paragraph,
        'summaryformat' => 1,
        'timecreated' => \Carbon\Carbon::now()->timestamp,
        'timemodified' => \Carbon\Carbon::now()->timestamp,
        'startdate' => \Carbon\Carbon::now()->timestamp,
        'enddate' => \Carbon\Carbon::now()->addDays(2)->timestamp,
    ];
});

$factory->define(\Zeizig\Moodle\Models\GradeCategory::class, function (Faker\Generator $faker) {
    return [
        'courseid'            => function () {
            return factory(\Zeizig\Moodle\Models\Course::class)->create()->id;
        },
        'path'                => '',
        'parent'              => function (array $gradeCategory) {
            $parentCat = factory(\Zeizig\Moodle\Models\GradeCategory::class)->create([
                'courseid' => $gradeCategory['courseid'],
                'parent'   => null,
            ]);
            return $parentCat->id;
        },
        'depth'               => function (array $gradeCategory) {
            return $gradeCategory['parent'] === null ? 1 : (\Zeizig\Moodle\Models\GradeCategory::find($gradeCategory['parent'])->depth + 1);
        },
        'fullname'            => $faker->sentence,
        'aggregation'         => 13,
        'keephigh'            => 0,
        'droplow'             => 0,
        'aggregateonlygraded' => function (array $gradeCategory) {
            return $gradeCategory['parent'] === null ? 1 : 0;
        },
        'aggregateoutcomes'   => 0,
        'timecreated' => \Carbon\Carbon::now()->timestamp,
        'timemodified' => \Carbon\Carbon::now()->timestamp,
        'hidden'              => 0,
    ];
});

$factory->define(\Zeizig\Moodle\Models\GradeItem::class, function (Faker\Generator $faker) {
    return [
        'courseid' => function () {
            return factory(\Zeizig\Moodle\Models\Course::class)->create()->id;
        },
        'categoryid' => null,
        'itemname' => null,
        'itemtype' => 'course',
        'itemmodule' => null,
        'iteminstance' => function (array $gradeItem) {
            return factory(\Zeizig\Moodle\Models\GradeCategory::class)->create([
                'courseid' => $gradeItem['courseid'],
                'parent' => null,
                'fullname' => '?',
            ])->id;
        },
        'itemnumber' => null,
    ];
}, 'course_grade_item');

$factory->define(\Zeizig\Moodle\Models\GradeItem::class, function (Faker\Generator $faker) {

    return [
        'courseid' => function () {
            return factory(\Zeizig\Moodle\Models\Course::class)->create()->id;
        },
        'categoryid' => null,
        'itemname' => null,
        'itemtype' => 'category',
        'itemmodule' => null,
        'iteminstance' => function (array $gradeItem) {
            return factory(\Zeizig\Moodle\Models\GradeCategory::class)->create([
                'courseid' => $gradeItem['courseid'],
            ])->id;
        },
        'grademax' => $faker->randomFloat(2, 0, 10)
    ];
}, 'grade_item_with_category');
