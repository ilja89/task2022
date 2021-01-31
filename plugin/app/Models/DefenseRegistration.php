<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\User;

/**
 * Defense registration model class.
 *
 * @property integer $id
 * @property integer $student_id // user
 * @property integer $charon_id // charon
 * @property integer $submission_id // charon_submission
 * @property integer $teacher_id // user
 * @property integer $lab_id // charon_lab
 * @property Carbon time
 * @property Carbon created_at
 * @property Carbon modified_at
 * @property string $progress values 'New', 'Pending', 'Waiting', 'Defending', 'Done'
 *
 * @package TTU\Charon\Model
 */
class DefenseRegistration extends Model {

    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'student_id', 'charon_id', 'submission_id', 'teacher_id', 'lab_id', 'time', 'progress'
    ];

    protected $table = 'charon_defense_registration';
    protected $dates = ['time'];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'student_id'); // owner key is "id" by default (?)
    }

    public function charon(){
        return $this->belongsTo(Charon::class, 'charon_id'); // or has one? :thinking:
    }

    public function submission(){
        return $this->hasOne(Submission::class, 'submission_id');
    }

    public function teacher(){
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lab(){
        return $this->belongsTo(Lab::class, 'lab_id');
    }


}
