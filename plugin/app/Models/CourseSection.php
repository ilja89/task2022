<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CourseSection.
 *
 * @property int id
 * @property int course
 * @property int section
 * @property string name
 * @property string sequence
 * @property int visible
 *
 * @package TTU\Charon\Models
 */
class CourseSection extends Model
{
    public $timestamps = false;
    protected $table = 'course_sections';
}
