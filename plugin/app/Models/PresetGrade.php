<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PresetGrade.
 *
 * @property int id
 * @property int preset_id
 * @property int grade_name_prefix_code
 * @property int grade_type_code
 * @property string grade_name
 * @property float max_result
 * @property string id_number_postfix
 *
 * @package TTU\Charon\Models
 */
class PresetGrade extends Model
{
    public $timestamps = false;

    protected $table = 'charon_preset_grade';

    public function preset()
    {
        return $this->belongsTo(Preset::class);
    }

    public function gradeNamePrefix()
    {
        return $this->belongsTo(GradeNamePrefix::class, 'grade_name_prefix_code', 'code');
    }

    public function gradeType()
    {
        return $this->belongsTo(GradeType::class, 'grade_type_code', 'code');
    }
}
