<?php

namespace TTU\Charon\Http\Controllers\Api;

use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;
use Illuminate\Http\Request;
use TTU\Charon\Repositories\CharonRepository;
use Zeizig\Moodle\Services\PermissionsService;

/**
 * Class FilesController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class FilesController extends Controller
{

    /** @var CharonRepository */
    private $charonRepository;

    /** @var PermissionsService */
    private $permissionsService;

    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        PermissionsService $permissionsService
    ) {
        parent::__construct($request);
        $this->charonRepository = $charonRepository;
        $this->permissionsService = $permissionsService;
    }
    /**
     * Get files for the given submission.
     *
     * @param  Submission  $submission
     *
     * @return SubmissionFile[]
     */
    public function index(Submission $submission)
    {
        // show test files only if the user has course management capabilities
        $courseId = $this->charonRepository->getCharonById($submission->charon_id)->course;
        $maxIsTest = 0;
        try {
            $this->permissionsService->requireCourseManagementCapability($courseId);
            $maxIsTest = 1;
        } catch (\required_capability_exception $e) {
        }
        $result = [];
        foreach ($submission->files as $submissionFile) {
            // legacy - for the case where "is_test" was not available
            // TODO: remove in new semester
            if ($submissionFile->is_test === null) {
                if ($maxIsTest > 0 || strpos($submissionFile->path, "Test") === false) {
                    // show only if has course management capability or doesn't have "Test" in path
                    $result[] = $submissionFile;
                }
            } else
            if ($submissionFile->is_test <= $maxIsTest) {
                $result[] = $submissionFile;
            }
        }
        return $result;
    }
}
