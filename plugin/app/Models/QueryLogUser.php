<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int user_id
 */
class QueryLogUser extends Model
{
    protected $table = 'charon_query_log_user';

    public $timestamps = false;

    protected $fillable = ['user_id'];
}
