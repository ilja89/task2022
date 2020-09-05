<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TesterType.
 *
 * @property integer $code
 * @property string $name
 *
 * @package TTU\Models\Charon
 */
class TesterType extends Model
{
    protected $fillable = ['name', 'code'];

    protected $table = 'charon_tester_type';

    public $timestamps = false;
}
