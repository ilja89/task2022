<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\User;

/**
 * Class SubmissionUser.
 *
 * @property integer user_id
 * @property integer submission_id
 * @property Submission submission
 * @property User user
 *
 * @package TTU\Charon\Models
 */
class SubmissionUser extends Model
{
    public $timestamps = false;
    protected $table = 'charon_submission_user';
    protected $fillable = [
        'user_id', 'submission_id'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
