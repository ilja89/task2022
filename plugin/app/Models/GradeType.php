<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GradeType.
 *
 * @property integer $code
 * @property string $name
 *
 * @package TTU\Models\Charon
 */
class GradeType extends Model
{
    protected $table = 'charon_grade_type';

    public $timestamps = false;
}
