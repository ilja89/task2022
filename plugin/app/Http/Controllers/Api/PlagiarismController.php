<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;

/**
 * Class PlagiarismController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class PlagiarismController extends Controller
{
    /**
     * Run the checksuite for the given Charon. Send a request to run the
     * checksuite to the plagiarism service.
     *
     * @param Charon $charon
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function runChecksuite(Charon $charon)
    {
        // TODO: Send request to server to rerun checksuite

        return response()->json([
            'message' => 'Plagiarism service has been notified to re-run the checksuite.',
        ], 200);
    }
}
