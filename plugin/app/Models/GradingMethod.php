<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GradingMethod.
 *
 * @property integer $code
 * @property string $name
 *
 * @package TTU\Models\Charon
 */
class GradingMethod extends Model
{
    protected $table = 'charon_grading_method';

    public $timestamps = false;
}
