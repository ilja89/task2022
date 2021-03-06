<?php

namespace Zeizig\Moodle\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class User.
 *
 * @property integer $id
 * @property string $idnumber
 * @property string $firstname
 * @property string $lastname
 * @property string $username
 *
 * @property Group[]|Collection $groups
 * @property GradeGrade[]|Collection $gradeGrades
 *
 * @package Zeizig\Moodle\Models
 */
class User extends Model
{
    public $timestamps = false;
    protected $table = 'user';

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'groups_members', 'userid', 'groupid');
    }

    public function gradeGrades()
    {
        return $this->hasMany(GradeGrade::class, 'userid', 'id');
    }
}
