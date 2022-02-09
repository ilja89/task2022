<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\User;

class QueryLogUsers extends Model
{
    public $timestamps = false;
    protected $table = 'charon_query_log_users';
    protected $fillable = [
        'user_id'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'id');
    }
}