<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\CourseModule;

/**
 * Charon model class.
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 *
 * @package TTU\Charon\Model
 */
class Charon extends Model
{

    /**
     * Whether this model uses timestamps or not.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description'
    ];

    /**
     * Required since Laravel thinks the table name should be charons.
     * Moodle however, wants plugin table names to be singular.
     *
     * @var string
     */
    protected $table = 'charon';

    /**
     * Get the course module associated with this charon instance.
     *
     * @return CourseModule
     */
    public function courseModule()
    {
        /** @var \Zeizig\Moodle\Services\ModuleService $moduleService */
        $moduleService = app(\Zeizig\Moodle\Services\ModuleService::class);

        return CourseModule::where('instance', $this->id)
                           ->where('module', $moduleService->getModuleId())
                           ->first();
    }
}
