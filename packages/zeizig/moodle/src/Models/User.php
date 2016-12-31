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
 * @package Zeizig\Moodle\Models
 */
class User extends Model
{
    public $timestamps = false;
}
