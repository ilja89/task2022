<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Deadline.
 *
 * @property int id
 * @property int lab_id
 * @property int charon_id
 *
 * @package TTU\Charon\Models
 */
class CharonDefenseLab extends Model
{
    public $timestamps = false;
    protected $table = 'charon_defense_lab';

    //protected $fillable = [
    //   'lab_id', 'charon_id'
    //];

}
