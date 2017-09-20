<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\Group;

/**
 * Class Deadline.
 *
 * @property integer $id
 * @property integer $charon_id
 * @property integer $percentage
 * @property Carbon $deadline_time
 * @property integer $group_id
 *
 * @property Charon $charon
 * @property Group $group
 *
 * @package TTU\Charon\Models
 */
class Deadline extends Model
{
    public $timestamps = false;
    protected $table = 'charon_deadline';

    protected $fillable = [
        'charon_id', 'deadline_time', 'percentage', 'group_id',
    ];

    public function charon()
    {
        return $this->belongsTo(Charon::class, 'charon_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function getDeadlineTimeAttribute($deadlineTime)
    {
        $deadlineTime = Carbon::parse($deadlineTime, 'UTC');
        $deadlineTime = $deadlineTime->setTimezone(config('app.timezone'));
        return $deadlineTime;
    }
}
