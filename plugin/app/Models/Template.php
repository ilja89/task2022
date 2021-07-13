<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Template.
 *
 * @property int id
 * @property int charon_id
 * @property string path
 * @property string contents
 * @property Carbon created_at
 *
 * @package TTU\Charon\Models
 */
class Template extends Model
{
    public $timestamps = false;

    protected $table = 'charon_template';
    protected $fillable = ['charon_id', 'path', 'contents', 'created_at'];
    protected $dates = ['created_at'];

}