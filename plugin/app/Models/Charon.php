<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\CourseModule;
use Zeizig\Moodle\Models\GradeCategory;
use Zeizig\Moodle\Models\GradeItem;

/**
 * Charon model class.
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $project_folder
 * @property string $extra
 * @property integer $tester_type_code
 * @property integer $grading_method_code
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property GradeCategory $category
 * @property Grademap[] $grademaps
 * @property Deadline[] $deadlines
 * @property int category_id
 *
 * @package TTU\Charon\Model
 */
class Charon extends Model
{

    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'project_folder', 'extra', 'tester_type_code', 'grading_method_code'
    ];

    /**
     * Required since Laravel thinks the table name should be charons.
     * Moodle however, wants plugin table names to be singular.
     *
     * @var string
     */
    protected $table = 'charon';

    public function grademaps()
    {
        return $this->hasMany(Grademap::class);
    }

    public function deadlines()
    {
        return $this->hasMany(Deadline::class);
    }

    public function testerType()
    {
        return $this->belongsTo(TesterType::class, 'tester_type_code', 'code');
    }

    public function gradingMethod()
    {
        return $this->belongsTo(GradingMethod::class, 'grading_method_code', 'code');
    }

    public function category()
    {
        return $this->belongsTo(GradeCategory::class, 'category_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
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

    /**
     * Gets the grade items associated with this Charon instance.
     *
     * @return GradeItem[]
     */
    public function gradeItems()
    {
        return GradeItem::where('itemtype', 'mod')
            ->where('itemmodule', config('moodle.plugin_slug'))
            ->where('iteminstance', $this->id)
            ->get();
    }
}
