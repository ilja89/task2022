<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\PlagiarismCommunicationService;

/**
 * Class PlagiarismController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class PlagiarismController extends Controller
{
    /** @var PlagiarismCommunicationService */
    private $plagiarismCommunicationService;

    /**
     * PlagiarismController constructor.
     *
     * @param PlagiarismCommunicationService $plagiarismCommunicationService
     */
    public function __construct(PlagiarismCommunicationService $plagiarismCommunicationService)
    {
        $this->plagiarismCommunicationService = $plagiarismCommunicationService;
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

        $response = $this->plagiarismCommunicationService->runChecksuite($checksuiteId);

        return response()->json([
            'message' => 'Plagiarism service has been notified to re-run the checksuite.',
        ], 200);
    }
}
