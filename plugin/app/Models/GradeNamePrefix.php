<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GradeNamePrefix.
 *
 * @property int code
 * @property string name
 *
 * @package TTU\Charon\Models
 */
class GradeNamePrefix extends Model
{
    public $timestamps = false;
    protected $table = 'charon_grade_name_prefix';
    protected $primaryKey = 'code';

    public function presetGrades()
    {
        return $this->hasMany(PresetGrade::class, 'grade_name_prefix_code', 'code');
    }
}
