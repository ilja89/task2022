<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int user_id
 */
class QueryLogUsers extends Model
{
    protected $table = 'charon_query_log_users';

    public $timestamps = false;

    protected $fillable = ['user_id'];
}
