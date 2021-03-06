<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\User;

/**
 * Class Comment.
 *
 * @property integer id
 * @property integer charon_id
 * @property integer student_id
 * @property integer teacher_id
 * @property string message
 * @property Carbon created_at
 *
 * @property Charon charon
 * @property User student
 * @property User teacher
 *
 * @package TTU\Charon\Models
 */
class Comment extends Model
{
    public $timestamps = false;
    protected $table = 'charon_teacher_comment';
    protected $fillable = [
        'charon_id', 'student_id', 'teacher_id', 'message', 'created_at'
    ];

    protected $dates = [ 'created_at' ];

    public function charon()
    {
        return $this->belongsTo(Charon::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }
}
