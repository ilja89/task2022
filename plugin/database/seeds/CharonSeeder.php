<?php

use Illuminate\Database\Seeder;
use TTU\Charon\Models\Charon;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\GradeCategory;
use Zeizig\Moodle\Models\GradeItem;

class CharonSeeder extends Seeder
{
    /**
     * Create Charons under an existing course.
     *
     * @return void
     */
    public function run()
    {
        $courseId = (int) $this->command->ask('Enter course ID');

        $course = Course::find($courseId);
        if (!$course) {
            $this->command->error('Course with ID ' . $courseId . ' not found');
            return;
        }

        $charons = (int) $this->command->ask('Enter a number of charons', 1);

        factory(Charon::class, $charons)->create([
            'course' => $courseId,
            'category_id' => function () use ($courseId) {
                $gradeItem = factory(GradeItem::class, 'grade_item_with_category')->create([
                    'courseid' => $courseId,
                ]);
                return GradeCategory::find($gradeItem->iteminstance)->id;
            }
        ]);
    }
}
