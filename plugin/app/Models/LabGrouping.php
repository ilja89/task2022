<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\Grouping;

/**
 * Class Comment.
 *
 * @property integer id
 * @property integer lab_id
 * @property integer group_id
 *
 * @package TTU\Charon\Models
 */
class LabGrouping extends Model
{
    public $timestamps = false;
    protected $table = 'charon_lab_grouping';
    protected $fillable = [
        'lab_id', 'grouping_id'
    ];

    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    public function groupings()
    {
        return $this->belongsTo(Grouping::class, 'grouping_id', 'id');
    }
}
