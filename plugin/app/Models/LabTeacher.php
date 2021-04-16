<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\User;

/**
 * Class Comment.
 *
 * @property integer id
 * @property integer lab_id
 * @property integer teacher_id
 * @property string teacher_location
 * @property string teacher_comment
 *
 * @package TTU\Charon\Models
 */
class LabTeacher extends Model
{
    public $timestamps = false;
    protected $table = 'charon_lab_teacher';
    protected $fillable = [
        'lab_id', 'teacher_id', 'teacher_location', 'teacher_comment'
    ];

    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }
}
