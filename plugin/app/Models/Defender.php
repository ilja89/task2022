<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Registration model class.
 *
 * @property integer $id
 * @property string $student_name
 * @property integer $submission_id
 * @property Carbon $choosen_time
 * @property boolean $my_teacher
 * @property integer $student_id
 * @property integer $defense_lab_id
 * @property integer $charon_id
 * @property integer $teacher_id
 * @property string $progress values 'Waiting', 'Defending', 'Done'
 *
 * @package TTU\Charon\Model
 */
class Defender extends Model {

    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'student_name', 'submission_id', 'choosen_time', 'my_teacher', 'student_id', 'defense_lab_id', 'progress', 'charon_id', 'teacher_id'
    ];

    protected $table = 'charon_defenders';
    public $timestamps = false;
}
