<?php

namespace TTU\Charon\Models;

use Illuminate\Database\Eloquent\Model;
use Zeizig\Moodle\Models\Course;

/**
 * Class CourseSettings.
 *
 * @property integer id
 * @property integer course_id
 * @property string unittests_git
 * @property integer tester_type_code
 * @property Course course
 * @property TesterType testerType
 * @property String tester_url
 * @property String tester_sync_url
 * @property String tester_token
 * @property PlagiarismLangType plagiarismLangType
 * @property GitlabLocationType gitlabLocationType
 * @property String plagiarism_language_type_code
 * @property String plagiarism_gitlab_group
 * @property integer gitlab_location_type_code
 * @property String plagiarism_file_extensions
 * @property integer plagiarism_moss_passes
 * @property integer plagiarism_moss_matches_shown
 *
 * @package TTU\Charon\Models
 */
class CourseSettings extends Model
{
    public $timestamps = false;
    protected $table = 'charon_course_settings';
    protected $fillable = [
        'course_id', 'unittests_git', 'tester_url', 'tester_token', 'tester_sync_url'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function testerType()
    {
        return $this->belongsTo(TesterType::class, 'tester_type_code', 'code');
    }

    public function plagiarismLangType()
    {
        return $this->belongsTo(PlagiarismLangType::class, 'plagiarism_language_type_code', 'code');
    }

    public function gitlabLocationType()
    {
        return $this->belongsTo(GitlabLocationType::class, 'gitlab_location_type_code', 'code');
    }
}
