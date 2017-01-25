<?php

namespace Zeizig\Moodle\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class User.
 *
 * @property integer $id
 * @property string $idnumber
 * @property string $firstname
 * @property string $lastname
 * @property string $username
 *
 * @property Group $group
 * @property GradeGrade[] $gradeGrades
 *
 * @package Zeizig\Moodle\Models
 */
class User extends Model
{
    public $timestamps = false;
    protected $table = 'user';

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members', 'userid', 'groupid');
    }

    public function gradeGrades()
    {
        return $this->hasMany(GradeGrade::class, 'userid', 'id');
    }
}
