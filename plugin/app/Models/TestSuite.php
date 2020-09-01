<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class TestSuite.
 *
 * @property int id
 * @property int submission_id
 * @property string name
 * @property string file
 * @property Carbon start_date
 * @property Carbon end_date
 * @property int weight
 * @property int passed_count
 * @property float grade
 *
 * @property UnitTest[]|Collection $unitTests
 *
 * @package TTU\Charon\Models
 */
class TestSuite extends Model
{
    public $timestamps = false;
    protected $table = 'charon_test_suite';

    protected $fillable = [
        'submission_id', 'name', 'file', 'start_date', 'end_date', 'weight', 'passed_count', 'grade'
    ];

    public function unitTests()
    {
        return $this->hasMany(UnitTest::class)->orderBy('id');
    }


    public function getDeadlineTimeAttribute($deadlineTime)
    {
        $deadlineTime = Carbon::parse($deadlineTime, 'UTC');
        $deadlineTime = $deadlineTime->setTimezone();
        return $deadlineTime;
    }
}