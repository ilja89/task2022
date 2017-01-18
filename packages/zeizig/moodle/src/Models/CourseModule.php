<?php

namespace Zeizig\Moodle\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Zeizig\Moodle\Services\ModuleService;

/**
 * Class CourseModule.
 * Moodle course module model.
 *
 * @property integer $id
 * @property integer $module
 * @property integer $instance
 * @property Course $moodleCourse
 *
 * @package Zeizig\Moodle\Modules
 */
class CourseModule extends Model
{
    public $timestamps = false;

    protected $table = 'course_modules';

    /**
     * Checks whether the given course module is an instance of this plugin.
     *
     * @return bool
     */
    public function isInstanceOfPlugin()
    {
        $moduleService = app(ModuleService::class);

        return $this->module === $moduleService->getModuleId();
    }

    /**
     * Declare the many to one association to Course.
     * This is not named course since using $courseModule->course would instead get the column value.
     *
     * @return BelongsTo
     */
    public function moodleCourse()
    {
        return $this->belongsTo(Course::class, 'course');
    }
}
