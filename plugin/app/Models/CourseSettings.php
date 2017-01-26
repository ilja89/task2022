<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\Course;

/**
 * Class CourseSettings.
 *
 * @property integer $id
 * @property integer $course_id
 * @property string $unittests_git
 * @property Course $course
 *
 * @package TTU\Charon\Models
 */
class CourseSettings extends Model
{
    public $timestamps = false;
    protected $table = 'charon_course_settings';
    protected $fillable = [
        'course_id', 'unittests_git'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }
}
