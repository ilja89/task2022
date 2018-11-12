<?php

namespace Zeizig\Moodle\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Grouping.
 *
 * @property integer $id
 * @property integer $courseid
 * @property string $idnumber
 * @property string $name
 * @property string $description
 *
 * @property Course $course
 * @property User[] $members
 *
 * @package Zeizig\Moodle\Models
 */
class Grouping extends Model
{
    public $timestamps = false;

    public function course()
    {
        return $this->belongsTo(Course::class, 'courseid');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'groupings_groups', 'groupingid', 'groupid');
    }

}
