<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\User;

/**
 * Defense registration model class.
 *
 * Possible values for progress:
 * New - no connected registrations
 * Booked - student has booked for defense
 * Pending - student has registered for defense
 * Defending - defense actively in progress
 * Done - defense is done
 * Expired - past lab time without a defense
 *
 * @property integer $id
 * @property integer $student_id
 * @property integer $charon_id
 * @property integer $submission_id
 * @property integer $teacher_id
 * @property integer $lab_id
 * @property Carbon $time
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $progress
 *
 * @package TTU\Charon\Model
 */
class DefenseRegistration extends Model
{
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

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function charon()
    {
        return $this->belongsTo(Charon::class, 'charon_id');
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }
}
