<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\User;

/**
 * Class Comment.
 *
 * @property integer id
 * @property integer lab_id
 * @property integer teacher_id
 *
 * @package TTU\Charon\Models
 */
class LabTeacher extends Model
{
    public $timestamps = false;
    protected $table = 'lab_teacher';
    protected $fillable = [
        'lab_id', 'teacher_id'
    ];

    //protected $dates = [ 'created_at' ];

    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }
}
