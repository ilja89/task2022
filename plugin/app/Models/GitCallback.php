<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class GitCallback.
 *
 * @property integer id
 * @property string url
 * @property string repo
 * @property string user
 * @property string secret_token
 * @property Carbon created_at
 * @property Carbon first_response_time
 *
 * @property Submission[]|Collection submissions
 *
 * @package TTU\Charon\Models
 */
class GitCallback extends Model
{
    public $timestamps = false;
    protected $table = 'charon_git_callback';
    protected $fillable = [
        'url', 'repo', 'user', 'created_at', 'secret_token'
    ];

    protected $dates = [ 'created_at', 'first_response_time' ];

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'git_callback_id', 'id');
    }
}
