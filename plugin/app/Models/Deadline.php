<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Deadline.
 *
 * @property integer $id
 * @property integer $charon_id
 * @property Carbon $deadline_time
 * @property integer $percentage
 * @property integer $group_id
 *
 * @property Charon $charon
 *
 * @package TTU\Charon\Models
 */
class Deadline extends Model
{
    public $timestamps = false;
    protected $table = 'charon_deadline';

    protected $fillable = [
        'charon_id', 'deadline_time', 'percentage'
    ];

    public function charon()
    {
        return $this->belongsTo(Charon::class, 'charon_id', 'id');
    }
}
