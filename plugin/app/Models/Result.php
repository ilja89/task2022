<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Result.
 *
 * @property integer $id
 * @property integer $submission_id
 * @property integer $grade_type_code
 * @property float $percentage
 * @property float $calculated_result
 * @property string $stdout
 * @property string $stderr
 *
 * @property Submission $submission
 * @property GradeType $gradeType
 *
 * @package TTU\Charon\Models
 */
class Result extends Model
{
    public $timestamps = false;
    protected $table = 'charon_result';
    protected $fillable = [
        'submission_id', 'grade_type_code', 'percentage', 'calculated_result', 'stdout', 'stderr'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id', 'id');
    }

    public function gradeType()
    {
        return $this->belongsTo(GradeType::class, 'grade_type_code', 'code');
    }
}
