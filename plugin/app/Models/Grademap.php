<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use TTU\Charon\Traits\HasGradeType;
use Zeizig\Moodle\Models\GradeItem;

/**
 * Class Grademap.
 *
 * @property integer id
 * @property string name
 * @property integer grade_item_id
 * @property int charon_id
 * @property bool persistent
 *
 * @property Charon charon
 * @property GradeItem gradeItem
 *
 * @package TTU\Charon\Models
 */
class Grademap extends Model
{
    use HasGradeType;

    protected $table = 'charon_grademap';
    public $timestamps = false;

    protected $fillable = [
        'charon_id', 'grade_type_code', 'name', 'grade_item_id', 'persistent'
    ];

    public function charon()
    {
        return $this->belongsTo(Charon::class, 'charon_id');
    }

    public function gradeItem()
    {
        return $this->hasOne(GradeItem::class, 'id', 'grade_item_id');
    }
}
