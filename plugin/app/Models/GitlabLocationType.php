<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GitlabLocationType.
 *
 * @property integer code
 * @property string name
 *
 * @package TTU\Models\Charon
 */
class GitlabLocationType extends Model
{
    protected $fillable = ['name', 'code'];

    protected $table = 'charon_gitlab_location_type';

    public $timestamps = false;
}
