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
 * @property string extra
 *
 * @package TTU\Charon\Models
 */
class Preset extends Model
{
    public $timestamps = false;

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
}
