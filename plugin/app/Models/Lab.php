<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Zeizig\Moodle\Models\Event;
use Zeizig\Moodle\Models\Group;
use Zeizig\Moodle\Models\User;

/**
 * Class Deadline.
 *
 * @property int id
 * @property Carbon start
 * @property Carbon end
 * @property int course_id
 *
 * @property User[]|Collection $teachers
 *
 * @package TTU\Charon\Models
 */
class Lab extends Model
{
    public $timestamps = false;
    protected $table = 'lab';

    protected $fillable = [
        'start', 'end', 'course_id'
    ];

    public function teachers()
    {
        return $this->hasMany(User::class)->orderBy('id');  // has no fillables?
    }


    public function getDeadlineTimeAttribute($deadlineTime)
    {
        $deadlineTime = Carbon::parse($deadlineTime, 'UTC');
        $deadlineTime = $deadlineTime->setTimezone(config('app.timezone'));
        return $deadlineTime;
    }
}