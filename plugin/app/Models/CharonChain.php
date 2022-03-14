<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

class CharonChain extends Model
{
    protected $fillable = [
        'charon_id','next_chain'
    ];

    public $timestamps = false;

    protected $table = 'charon_chain';
}
