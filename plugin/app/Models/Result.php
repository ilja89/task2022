<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use TTU\Charon\Traits\HasGradeType;
use Zeizig\Moodle\Models\User;

/**
 * Class Result.
 *
 * @property integer id
 * @property integer submission_id
 * @property integer user_id
 * @property float percentage
 * @property float calculated_result
 * @property string stdout
 * @property string stderr
 *
 * @property Submission $submission
 *
 * @package TTU\Charon\Models
 */
class Result extends Model
{
    use HasGradeType;

    public $timestamps = false;
    protected $table = 'charon_result';
    protected $fillable = [
        'submission_id', 'user_id', 'grade_type_code', 'percentage', 'calculated_result', 'stdout', 'stderr'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get grademap for the current result.
     *
     * @return Grademap
     */
    public function getGrademap()
    {
        return Grademap::where('charon_id', $this->submission->charon_id)
            ->where('grade_type_code', $this->grade_type_code)
            ->first();
    }
}
