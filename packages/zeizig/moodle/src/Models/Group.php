<?php

namespace Zeizig\Moodle\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Group.
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
class Group extends Model
{
    public $timestamps = false;

    public function course()
    {
        return $this->belongsTo(Course::class, 'courseid');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'groups_members', 'groupid', 'userid');
    }
}
