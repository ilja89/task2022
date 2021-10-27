<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Defenders.
 *
 * @property integer id
 * @property integer student_id
 * @property integer charon_id
 * @property string student_name
 * @property integer submission_id
 * @property Carbon choosen_time
 * @property integer my_teacher
 * @property integer teacher_id
 * @property integer defense_lab_id
 * @property string progress
 *
 * @package TTU\Charon\Models
 */
class Defenders extends Model
{
    public $timestamps = false;
    protected $table = 'charon_defenders';

    protected $fillable = [
        'student_id', 'charon_id', 'student_name', 'submission_id', 'choosen_time',
        'my_teacher', 'teacher_id', 'defense_lab_id', 'progress'
    ];
}
