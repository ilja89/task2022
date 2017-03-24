<?php

namespace TTU\Charon\Http\Controllers\Api;

use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;

/**
 * Class FilesController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class FilesController extends Controller
{
    /**
     * Get files for the given submission.
     *
     * @param  Submission  $submission
     *
     * @return SubmissionFile[]
     */
    public function index(Submission $submission)
    {
        return $submission->files;
    }
}
