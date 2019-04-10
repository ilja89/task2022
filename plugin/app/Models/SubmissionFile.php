<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SubmissionFile.
 *
 * @property integer $id
 * @property integer $submission_id
 * @property integer $is_test
 * @property string $path
 * @property string $contents
 *
 * @property Submission $submission
 *
 * @package TTU\Charon\Models
 */
class SubmissionFile extends Model
{
    public $timestamps = false;
    protected $table = 'charon_submission_file';
    protected $fillable = [
        'submission_id', 'path', 'contents', 'is_test'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id', 'id');
    }
}
