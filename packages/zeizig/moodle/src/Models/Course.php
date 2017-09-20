<?php

namespace Zeizig\Moodle\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Course
 *
 * @property integer $id
 * @property string $fullname
 * @property string $shortname
 * @property CourseModule[]|Collection $courseModules
 * @property Group[]|Collection $groups
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
}
