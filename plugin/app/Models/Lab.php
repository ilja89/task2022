<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Zeizig\Moodle\Models\User;

/**
 * Class Lab.
 *
 * @property int id
 * @property string name
 * @property Carbon start
 * @property Carbon end
 * @property int course_id
 *
 * @property User[]|Collection teachers
 *
 * @package TTU\Charon\Models
 */
class Lab extends Model
{
    public $timestamps = false;

    protected $table = 'charon_lab';
    protected $fillable = ['name', 'start', 'end', 'course_id'];
    protected $dates = ['start', 'end',];

    public function teachers()
    {
        return $this->hasMany(User::class)->orderBy('id');
    }

    public function getDeadlineTimeAttribute($deadlineTime)
    {
        return Carbon::parse($deadlineTime);
    }
}
