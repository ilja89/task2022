<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\PlagiarismService;

/**
 * Class PlagiarismController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class PlagiarismController extends Controller
{
    /** @var PlagiarismService */
    private $plagiarismService;

    /**
     * PlagiarismController constructor.
     *
     * @param Request $request
     * @param PlagiarismService $plagiarismService
     */
    public function __construct(Request $request, PlagiarismService $plagiarismService)
    {
        parent::__construct($request);
        $this->plagiarismService = $plagiarismService;
    }

    /**
     * Run the checksuite for the given Charon. Send a request to run the
     * checksuite to the plagiarism service.
     *
     * @param Charon $charon
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function runChecksuite(Charon $charon)
    {
        $checksuiteId = $charon->plagiarism_checksuite_id;

        if (!$checksuiteId) {
            return response()->json([
                'message' => 'The given Charon does not have plagiarism enabled.',
            ], 400);
        }

        $this->plagiarismService->runChecksuite($charon);

        return response()->json([
            'message' => 'Plagiarism service has been notified to re-run the checksuite.',
        ], 200);
    }

    /**
     * Fetch the similarities for the latest check of the given Charon.
     *
     * @param Charon $charon
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fetchSimilarities(Charon $charon)
    {
        if (!$charon->plagiarism_latest_check_id && !$charon->plagiarism_checksuite_id) {
            return response()->json([
                'message' => 'The given Charon does not have plagiarism enabled.',
            ], 400);
        } else if (!$charon->plagiarism_latest_check_id && $charon->plagiarism_checksuite_id) {
            $charon = $this->plagiarismService->runChecksuite($charon);
        }

        $similarities = $this->plagiarismService->getLatestSimilarities($charon);

        return response()->json([
            'similarities' => $similarities,
        ], 200);
    }
}
