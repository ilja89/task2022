<?php

namespace TTU\Charon\Models;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\Event;
use Zeizig\Moodle\Models\Group;

/**
 * Class Deadline.
 *
 * @property int id
 * @property int charon_id
 * @property int percentage
 * @property Carbon deadline_time
 * @property int group_id
 * @property int event_id
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

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

//    public function getDeadlineTimeAttribute($deadlineTime)
//    {
//        $deadlineTime = Carbon::parse($deadlineTime, 'UTC');
//        if (config('app.timezone')) {
//            $deadlineTime = $deadlineTime->setTimezone(config('app.timezone'));
//        }
//        return $deadlineTime;
//    }
}
