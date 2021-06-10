<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\Group;

/**
 * Class Comment.
 *
 * @property integer id
 * @property integer lab_id
 * @property integer group_id
 *
 * @package TTU\Charon\Models
 */
class LabGroup extends Model
{
    public $timestamps = false;

    protected $table = 'charon_lab_group';
    protected $fillable = [
        'lab_id', 'group_id'
    ];

    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    public function groups()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }
}
