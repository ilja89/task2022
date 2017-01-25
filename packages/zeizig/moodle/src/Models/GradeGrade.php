<?php

namespace Zeizig\Moodle\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GradeGrade.
 *
 * @property integer $id
 * @property integer $itemid
 * @property integer $userid
 * @property float $rawgrade
 * @property float $finalgrade
 *
 * @property GradeItem $gradeItem
 * @property User $user
 *
 * @package Zeizig\Moodle\Models
 */
class GradeGrade extends Model
{
    public $timestamps = false;

    public function gradeItem()
    {
        return $this->belongsTo(GradeItem::class, 'itemid', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userid', 'id');
    }
}
