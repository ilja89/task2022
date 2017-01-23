<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\GradeItem;

/**
 * Class Grademap.
 *
 * @property integer $id
 * @property string $name
 * @property integer grade_item_id
 * @property integer grade_type_code
 *
 * @property Charon $charon
 * @property GradeItem $gradeItem
 * @property GradeType $gradeType
 *
 * @package TTU\Charon\Models
 */
class Grademap extends Model
{
    protected $table = 'charon_grademap';
    public $timestamps = false;

    protected $fillable = [
        'charon_id', 'grade_type_code', 'name'
    ];

    public function charon()
    {
        return $this->belongsTo(Charon::class, 'charon_id');
    }

    public function gradeItem()
    {
        return $this->hasOne(GradeItem::class, 'id', 'grade_item_id');
    }

    public function gradeType()
    {
        return $this->belongsTo(GradeType::class, 'grade_type_code', 'code');
    }
}
