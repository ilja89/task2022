<?php

namespace Zeizig\Moodle\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Event.
 *
 * @property int id
 * @property string name
 * @property string description
 * @property int courseid
 * @property int groupid
 * @property int userid
 * @property string modulename
 * @property int instance
 * @property int type
 * @property string eventtype
 * @property int timestart - timestamp
 *
 * @package Zeizig\Moodle\Models
 */
class Event extends Model
{
    public $timestamps = false;

    protected $table = 'event';
}