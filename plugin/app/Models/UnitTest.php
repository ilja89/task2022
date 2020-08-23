<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Deadline.
 *
 * @property int id
 * @property int test_suite_id
 * @property string groups_depended_upon
 * @property string status
 * @property int weight
 * @property boolean print_exception_message
 * @property boolean print_stack_trace
 * @property int time_elapsed
 * @property string methods_depended_upon
 * @property string stack_trace
 * @property string name
 * @property string stdout
 * @property string exception_class
 * @property string exception_message
 * @property string stderr
 *
 * @package TTU\Charon\Models
 */
class UnitTest extends Model
{
    public $timestamps = false;
    protected $table = 'charon_unit_test';

    protected $fillable = [
        'test_suite_id', 'groups_depended_upon', 'status', 'weight', 'print_exception_message', 'print_stack_trace',
        'time_elapsed', 'methods_depended_upon', 'stack_trace', 'name', 'stdout', 'exception_class',
        'exception_message', 'stderr'
    ];

}