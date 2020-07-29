<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Charon model class.
 *
 * @property string $student_name
 * @property integer $submission_id
 * @property Carbon $choosen_time
 * @property boolean $my_teacher
 * @property integer $student_id
 *
 * @package TTU\Charon\Model
 */
class Defenders extends Model {
    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'student_name', 'submission_id', 'choosen_time', 'my_teacher', 'student_id'
    ];
    protected $table = 'defenders';
    public $timestamps = false;
}
