<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Models\Group;

/**
 * Class Lab.
 *
 * @property int id
 * @property string name
 * @property Carbon start
 * @property Carbon end
 * @property int course_id
 * @property string type
 *
 * @property User[]|Collection teachers
 *
 * @package TTU\Charon\Models
 */
class Lab extends Model
{
    public $timestamps = false;

    protected $table = 'charon_lab';
    protected $fillable = ['name', 'start', 'end', 'course_id', 'type'];
    protected $dates = ['start', 'end',];

    public function teachers()
    {
        return $this->hasMany(User::class)->orderBy('id');
    }

    public function groups()
    {
        return $this->hasMany(Group::class)->orderBy('id');
    }

    public function getDeadlineTimeAttribute($deadlineTime)
    {
        return Carbon::parse($deadlineTime);
    }
}
