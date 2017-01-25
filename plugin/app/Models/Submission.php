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
 * @property string $git_hash
 * @property Carbon $git_timestamp
 * @property string $mail
 * @property string $stdout
 * @property string $stderr
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Charon $charon
 * @property User $user
 * @property Result[] $results
 * @property SubmissionFile[] $files
 *
 * @package TTU\Charon\Models
 */
class Submission extends Model
{
    public $table = 'charon_submission';
    protected $fillable = [
        'charon_id', 'user_id', 'git_hash', 'git_timestamp', 'mail', 'stdout', 'stderr'
    ];

    protected $dates = [
        'git_timestamp'
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

    public function getGitTimestampAttribute($gitTimestamp)
    {
        $gitTimestamp = Carbon::createFromFormat('Y-m-d H:i:s', $gitTimestamp, 'UTC');
        $gitTimestamp->setTimezone(config('app.timezone'));
        return $gitTimestamp;
    }
}
