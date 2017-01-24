<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\SubmissionService;

/**
 * Class TesterCallbackController.
 * Handles accepting submissions and results from the tester.
 *
 * @package TTU\Charon\Http\Controllers
 */
class TesterCallbackController extends Controller
{
    /** @var Request */
    protected $request;

    /** @var SubmissionService */
    private $submissionService;

    /**
     * TesterCallbackController constructor.
     *
     * @param Request $request
     * @param SubmissionService $submissionService
     */
    public function __construct(Request $request, SubmissionService $submissionService)
    {
        $this->request = $request;
        $this->submissionService = $submissionService;
    }

    /**
     * Accepts submissions from the tester.
     */
    public function index()
    {
        $submission = $this->submissionService->saveSubmission($this->request);
        return $submission;
    }
}
