<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\User;

/**
 * Class PlagiarismCheck.
 *
 * @property integer id
 * @property integer charon_id
 * @property integer user_id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string status
 *
 * @property Charon charon
 * @property User user
 *
 * @package TTU\Charon\Models
 */
class PlagiarismCheck extends Model
{
    public $timestamps = false;
    protected $table = 'charon_plagiarism_check';
    protected $fillable = [
        'charon_id', 'user_id', 'created_at', 'updated_at', 'status', 'run_id'
    ];

    protected $dates = [ 'created_at', 'updated_at'];

    public function charon()
    {
        return $this->belongsTo(Charon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
