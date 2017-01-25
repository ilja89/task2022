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

    public function isTestsGrade()
    {
        return $this->code < 100;
    }

    public function isStyleGrade()
    {
        return $this->code > 100 && $this->code < 1000;
    }

    public function isCustomGrade()
    {
        return $this->code > 1000;
    }
}
