<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GitCallback.
 *
 * @property integer $id
 * @property string $url
 * @property string $repo
 * @property string $user
 * @property string $secret_token
 * @property integer $response_received
 * @property Carbon $created_at
 * @property Carbon first_response_time
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
}
