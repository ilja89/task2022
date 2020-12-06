<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\Course;

/**
 * Class CourseSettings.
 *
 * @property integer id
 * @property integer course_id
 * @property string unittests_git
 * @property integer tester_type_code
 * @property Course course
 * @property TesterType testerType
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

    public function testerType()
    {
        return $this->belongsTo(TesterType::class, 'tester_type_code', 'code');
    }
}
