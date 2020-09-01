<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\User;

/**
 * Class Submission.
 *
 * @property integer $id
 * @property integer $charon_id
 * @property integer $user_id
 * @property string $git_hash
 * @property integer $confirmed
 * @property Carbon $git_timestamp
 * @property string $mail
 * @property string $stdout
 * @property string $stderr
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $git_callback_id
 * @property int $original_submission_id
 * @property int $grader_id
 *
 * @property Charon $charon
 * @property User $user
 * @property Result[] $results
 * @property SubmissionFile[] $files
 * @property GitCallback $gitCallback
 * @property Submission $originalSubmission
 * @property TestSuite[] $testSuites
 *
 * @package TTU\Charon\Models
 */
class Submission extends Model
{
    public $table = 'charon_submission';
    protected $fillable = [
        'charon_id', 'user_id', 'git_hash', 'git_timestamp', 'mail', 'stdout', 'stderr', 'git_commit_message',
        'created_at', 'updated_at', 'original_submission_id',
    ];

    protected $dates = [
        'git_timestamp', 'created_at', 'updated_at'
    ];

    public function charon()
    {
        return $this->belongsTo(Charon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function files()
    {
        return $this->hasMany(SubmissionFile::class);
    }

    public function gitCallback()
    {
        return $this->belongsTo(GitCallback::class, 'git_callback_id', 'id');
    }

    public function originalSubmission()
    {
        return $this->belongsTo(Submission::class, 'original_submission_id', 'id');
    }

    public function grader()
    {
        return $this->belongsTo(User::class, 'grader_id', 'id');
    }

    public function testSuites()
    {
        return $this->hasMany(TestSuite::class);
    }

    public function getGitTimestampAttribute($gitTimestamp)
    {
        $gitTimestamp = Carbon::createFromFormat('Y-m-d H:i:s', $gitTimestamp, 'UTC');
        return $gitTimestamp->toDateTimeString();
    }

    public function getCreatedAtAttribute($createdAt)
    {
        $createdAt = Carbon::createFromFormat('Y-m-d H:i:s', $createdAt, 'UTC');
        return $createdAt->toDateTimeString();
    }

    public function getUpdatedAtAttribute($updatedAt)
    {
        $updatedAt = Carbon::createFromFormat('Y-m-d H:i:s', $updatedAt, 'UTC');
        return $updatedAt->toDateTimeString();
    }
}
