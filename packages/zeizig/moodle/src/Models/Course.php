<?php

namespace Zeizig\Moodle\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class Course
 *
 * @property integer $id
 * @property string $fullname
 * @property string $shortname
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

    /**
     * @param $courseId
     * @return mixed
     */
    public function getNamesOfStudentsRelatedToCourse($courseId)
    {
        return DB::table('role_assignments')
            ->join('user', 'role_assignments.userid', '=', 'user.id')
            ->join('context', 'role_assignments.contextid', '=', 'context.id')
            ->where('context.contextlevel', CONTEXT_COURSE)
            ->where('context.instanceid', $courseId)
            ->select('username')
            ->get()
            ->all();
    }
}
