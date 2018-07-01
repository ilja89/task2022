<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\GradeCategory;

/**
 * Class Preset.
 *
 * @property int id
 * @property string name
 * @property int parent_category_id
 * @property int course_id
 * @property string calculation_formula
 * @property string tester_extra
 * @property string system_extra
 * @property int grading_method_code
 * @property float max_result
 *
 * @property PresetGrade[] presetGrades
 * @property Course course
 * @property GradeCategory parentCategory
 *
 * @package TTU\Charon\Models
 */
class Preset extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name', 'parent_category_id', 'course_id', 'calculation_formula',
        'tester_extra', 'system_extra', 'max_result', 'grading_method_code',
    ];

    protected $table = 'charon_preset';

    public function parentCategory()
    {
        return $this->belongsTo(GradeCategory::class, 'parent_category_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function presetGrades()
    {
        return $this->hasMany(PresetGrade::class);
    }

    public function gradingMethod()
    {
        return $this->belongsTo(GradingMethod::class, 'grading_method_code', 'code');
    }
}
