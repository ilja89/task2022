<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PlagiarismLangType.
 *
 * @property integer code
 * @property string name
 *
 * @package TTU\Models\Charon
 */
class PlagiarismLangType extends Model
{
    protected $fillable = ['name', 'code'];

    protected $table = 'charon_plagiarism_lang_type';

    public $timestamps = false;
}
