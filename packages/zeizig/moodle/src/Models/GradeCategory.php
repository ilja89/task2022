<?php

namespace Zeizig\Moodle\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GradeCategory.
 *
 * @property integer $id
 * @property string $fullname
 *
 * @property GradeCategory $parentCategory
 * @property Course $course
 * @property GradeItem[] $gradeItems
 *
 * @package Zeizig\Moodle\Models
 */
class GradeCategory extends Model
{
    public $timestamps = false;

    public function parentCategory()
    {
        return $this->belongsTo(GradeCategory::class, 'parent');
    }

    public function gradeItems()
    {
        return $this->hasMany(GradeItem::class, 'categoryid');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'courseid');
    }

    /**
     * Get the corresponding grade item for this category.
     *
     * @return  GradeItem
     */
    public function getGradeItem()
    {
        return GradeItem::where('itemtype', 'category')
            ->where('iteminstance', $this->id)
            ->first();
    }
}
