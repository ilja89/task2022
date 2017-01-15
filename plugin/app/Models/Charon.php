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
 * @property string $project_folder
 * @property integer $tester_type_code
 * @property integer $grading_method_code
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
        'name', 'description', 'project_folder', 'tester_type_code', 'grading_method_code'
    ];

    /**
     * Required since Laravel thinks the table name should be charons.
     * Moodle however, wants plugin table names to be singular.
     *
     * @var string
     */
    protected $table = 'charon';

    public function testerType()
    {
        return $this->belongsTo(TesterType::class, 'tester_type_code', 'code');
    }

    public function gradingMethod()
    {
        return $this->belongsTo(GradingMethod::class, 'grading_method_code', 'code');
    }

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
