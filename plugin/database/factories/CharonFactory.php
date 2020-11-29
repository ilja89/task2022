<?php

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CourseSettings;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\TesterType;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\GradeCategory;
use Zeizig\Moodle\Models\GradeItem;

/** @var Factory $factory */
$factory->define(Charon::class, function (Generator $faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->paragraph,
        'project_folder' => $faker->word,
        'tester_extra' => $faker->word,
        'system_extra' => $faker->word,
        'tester_type_code' => $faker->randomElement([1, 2, 3]),
        'grading_method_code' => $faker->randomElement([1, 2]),
        'grouping_id' => $faker->randomElement([1, 2]),
        'defense_deadline' => Carbon::parse($faker->unixTime)->format('Y-m-d H:i:s'),
        'defense_duration' => $faker->numberBetween(0, 10),
        'choose_teacher' => $faker->boolean,
        'timemodified' => $faker->unixTime,
    ];
});

$factory->state(Charon::class, 'with_new_course', function (Generator $faker) {
    $courseGradeItem = factory(GradeItem::class, 'course_grade_item')->create();

    return [
        'course' => $courseGradeItem->courseid,
        'category_id' => function () use ($courseGradeItem) {
            $gradeItem = factory(GradeItem::class, 'grade_item_with_category')->create([
                'courseid' => $courseGradeItem->courseid,
            ]);
            return GradeCategory::find($gradeItem->iteminstance)->id;
        }
    ];
});

$factory->define(CourseSettings::class, function (Generator $faker) {
    return [
        'course_id' => function () {
            return factory(Course::class)->create()->id;
        },
        'unittests_git' => $faker->word,
        'tester_type_code' => $faker->randomElement([1, 2, 3, 4]),
    ];
});

$factory->define(TesterType::class, function (Generator $faker) {
    return [
        'name' => $faker->word,
        'code' => $faker->word,
    ];
});

$factory->define(Submission::class, function (Generator $faker) {
    return [
        'charon_id' => 0,
        'user_id' => 0,
        'git_hash' => $faker->word,
        'git_timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
        'mail' => $faker->email,
        'stdout' => $faker->sentence,
        'stderr' => $faker->sentence,
        'git_commit_message' => $faker->sentence,
        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
    ];
});

$factory->define(GitCallback::class, function (Generator $faker) {
    return [
        'url' => $faker->url,
        'secret_token' => $faker->word,
        'repo' => $faker->word,
        'user' => $faker->firstName,
        'created_at' => Carbon::now()->format('Y-m-d H:i:s')
    ];
});
