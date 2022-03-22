<?php

namespace TTU\Charon\Http\Controllers\Api;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\PlagiarismCheck;
use TTU\Charon\Services\PlagiarismService;
use Zeizig\Moodle\Models\Course;

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
     * @throws GuzzleException
     */
    public function runChecksuite(Charon $charon)
    {
        $checksuiteId = $charon->plagiarism_checksuite_id;

        if (!$checksuiteId) {
            return response()->json([
                'message' => 'This Charon does not have plagiarism enabled.',
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
     * @throws GuzzleException
     */
    public function fetchSimilarities(Charon $charon)
    {
        if (!$charon->plagiarism_latest_check_id && !$charon->plagiarism_checksuite_id) {
            return response()->json([
                'message' => 'This Charon does not have plagiarism enabled.',
            ], 400);
        } else if (!$charon->plagiarism_latest_check_id && $charon->plagiarism_checksuite_id) {
            $charon = $this->plagiarismService->runChecksuite($charon);
        }

        $similarities = $this->plagiarismService->getLatestSimilarities($charon);

        return response()->json([
            'similarities' => $similarities,
        ], 200);
    }

    /**
     * Fetch the matches for the given Charon.
     *
     * @param Course $course
     * @param Charon $charon
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function fetchMatches(Course $course, Charon $charon): array
    {
        return $this->plagiarismService->getMatches($charon, $course);
    }

    /**
     * Run the checks for the given Charon. Send a request to run the
     * check to the plagiarism service.
     *
     * @param Course $course
     * @param Charon $charon
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws GuzzleException
     */
    public function runCheck(Course $course, Charon $charon): \Illuminate\Http\JsonResponse
    {
        $status = $this->plagiarismService->runCheck($charon, $course, $this->request);

        if ($status['status'] == "Could not connect to Plagiarism application"
            or $status['status'] == "Unexpected error") {
            return response()->json([
                'message' => 'Error when trying to connect to Plagiarism api',
                'status' => $status
            ]);
        } else {
            return response()->json([
                'message' => 'Plagiarism service has been notified to re-run the checksuite.',
                'status' => $status
            ]);
        }
    }

    /**
     * Returns the status of the asked plagiarism check.
     *
     * @param Charon $charon
     * @param PlagiarismCheck $plagiarismCheck
     * @return array
     */
    public function getStatus(Charon $charon, PlagiarismCheck $plagiarismCheck): array
    {
        return [
            "charonName" => $charon->name,
            "created_at" => $plagiarismCheck->created_at,
            "updated_at" => $plagiarismCheck->updated_at,
            "status" => $plagiarismCheck->status,
            "checkId" => $plagiarismCheck->id,
            "author" => $plagiarismCheck->user->firstname . ' ' . $plagiarismCheck->user->lastname
        ];
    }

    /**
     * Returns a list of this courses plagiarism checks.
     *
     * @param Course $course
     * @return array
     */
    public function getCheckHistory(Course $course): array
    {
        return $this->plagiarismService->getCheckHistory($course);
    }
}
