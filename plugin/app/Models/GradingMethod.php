<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GradingMethod.
 *
 * @property integer code
 * @property string name
 *
 * @package TTU\Models\Charon
 */
class GradingMethod extends Model
{
    public $timestamps = false;

    protected $table = 'charon_grading_method';

    protected $primaryKey = 'code';

    public function isPreferBest()
    {
        return $this->code === 1;
    }

    public function isPreferLast()
    {
        return $this->code === 2;
    }

    public function isPreferBestEachGrade()
    {
        return $this->code === 3;
    }
}
