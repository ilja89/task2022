<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PlagiarismService.
 *
 * The plagiarism checking service (jplag, moss, etc.) that the plagiarism
 * service (julia) uses for plagiarism checks.
 *
 * @property int code
 * @property string name
 *
 * @package TTU\Charon\Models
 */
class PlagiarismService extends Model
{
    public $timestamps = false;

    protected $table = 'charon_plagiarism_service';

    protected $primaryKey = 'code';
}
