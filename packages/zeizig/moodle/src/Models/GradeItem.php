<?php

namespace Zeizig\Moodle\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GradeItem.
 *
 * @property integer $id
 * @property integer $categoryid
 * @property string $itemname
 * @property integer $iteminstance
 * @property string $itemnumber
 * @property string $calculation
 * @property double $grademax
 * @property string idnumber
 *
 * @property Course $course
 * @property GradeCategory $category
 * @property GradeGrade $gradeGrades
 *
 * @package Zeizig\Moodle\Models
 */
class GradeItem extends Model
{
    public $timestamps = false;

    public function course()
    {
        return $this->belongsTo(Course::class, 'courseid');
    }

    public function category()
    {
        return $this->belongsTo(GradeCategory::class, 'categoryid');
    }

    public function gradeGrades()
    {
        return $this->hasMany(GradeGrade::class, 'itemid', 'id');
    }

    public function gradesForUser($userId)
    {
        return $this->gradeGrades->first(function ($gradeGrade) use ($userId) {
            /** @var GradeGrade $gradeGrade */
            return $gradeGrade->userid == $userId;
        });
    }
}
