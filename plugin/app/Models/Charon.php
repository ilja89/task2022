<?php

namespace TTU\Charon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\CourseModule;
use Zeizig\Moodle\Models\GradeCategory;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Models\Grouping;
use Zeizig\Moodle\Services\ModuleService;

/**
 * Charon model class.
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $project_folder
 * @property string $tester_extra
 * @property string $system_extra
 * @property integer $tester_type_code
 * @property integer $grading_method_code
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int category_id
 * @property int $grouping_id
 * @property int course
 * @property int timemodified
 * @property string|null plagiarism_checksuite_id - Id of the associated
 *      checksuite in the Julia plagiarism service.
 * @property string|null plagiarism_latest_check_id - Id of the latest check
 *      for this Charon in the Julia plagiarism service.
 * @property Carbon defense_deadline
 * @property int defense_duration
 * @property bool choose_teacher
 *
 * @property GradeCategory $category
 * @property GradingMethod $gradingMethod
 * @property TesterType $testerType
 * @property Grademap[] $grademaps
 * @property Deadline[]|Collection $deadlines
 * @property CharonDefenseLab[]|Collection $charonDefenseLabs
 * @property Course moodleCourse
 * @property Grouping $grouping
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
        'name', 'description', 'project_folder', 'tester_extra', 'system_extra',
        'tester_type_code', 'grading_method_code', 'course', 'grouping_id', 'timemodified',
        'defense_deadline', 'defense_duration', 'choose_teacher', 'charon_defense_labs'
    ];

    /**
     * Required since Laravel thinks the table name should be charons. Moodle
     * however, wants plugin table names to be singular.
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
        return $this->hasMany(Deadline::class)->orderBy('deadline_time');
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

    public function grouping()
    {
        return $this->belongsTo(Grouping::class, 'grouping_id');
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
        /** @var ModuleService $moduleService */
        $moduleService = app(ModuleService::class);

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

    /**
     * Get the grade types for this Charon instance.
     * Returns grade type codes for all grademaps.
     *
     * @return int[]
     */
    public function getGradeTypeCodes()
    {
        return $this->grademaps->map(function ($grademap) {
            return $grademap->grade_type_code;
        });
    }

    public function moodleCourse()
    {
        return $this->belongsTo(Course::class, 'course', 'id');
    }
}
