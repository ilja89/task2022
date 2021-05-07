<?php

namespace Zeizig\Moodle\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Course
 *
 * @property integer $id
 * @property string $fullname
 * @property string $shortname
 * @property Carbon $startdate
 * @property Carbon $enddate
 * @property CourseModule[]|Collection $courseModules
 * @property Group[]|Collection $groups
 * @property Grouping[]|Collection $groupings
 *
 * @package TTU\Moodle\Model
 */
class Course extends Model
{
    protected $table = 'course';

    public $timestamps = false;

    protected $dates = ['startdate', 'enddate'];

    /**
     * Declare the one to many relationship with the course module table.
     *
     * @return HasMany
     */
    public function courseModules()
    {
        return $this->hasMany(CourseModule::class, 'course', 'id');
    }

    /**
     * @return HasMany
     */
    public function gradeItems()
    {
        return $this->hasMany(GradeItem::class, 'courseid', 'id');
    }

    /**
     * @return HasMany
     */
    public function gradeCategories()
    {
        return $this->hasMany(GradeCategory::class, 'courseid');
    }

    /**
     * @return HasMany
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'courseid');
    }

    /**
     * @return HasMany
     */
    public function groupings()
    {
        return $this->hasMany(Grouping::class, 'courseid');
    }

    /**
     * Get the course ID by name.
     *
     * @return integer
     */
    public function getCourseByName($courseName)
    {
        $course = Course::select('id')->where('shortname', $courseName)->first();
        return $course->id;
    }
}
