<?php

namespace TTU\Charon\Http\Controllers\Api;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\PlagiarismService;
use Zeizig\Moodle\Globals\User;
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
     * Fetch the matches for the given Charon.
     * Also returns times of plagiarism runs.
     *
     * @param Charon $charon
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function fetchMatches(Charon $charon): array
    {
        return $this->plagiarismService->getMatches($charon);
    }

    /**
     * Fetch the matches for the given Charon by plagiarism run
     *
     * @param Request $request
     *
     * @return array
     *
     * @throws GuzzleException
     */
    public function fetchMatchesByRun(Request $request): array
    {
        return $this->plagiarismService->getMatchesByRun($request->input('run_id'));
    }

    /**
     * Run the checks for the given Charon. Send a request to run the
     * check to the plagiarism service.
     *
     * @param Charon $charon
     *
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function runCheck(Charon $charon): JsonResponse
    {
        $status = $this->plagiarismService->runCheck($charon, app(User::class)->currentUser());

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
     * @param Request $request
     * @return array
     * @throws GuzzleException
     */
    public function getLatestStatus(Request $request): array
    {
        return $this->plagiarismService->getLatestStatus($request->input('run_id'));
    }

    /**
     * Returns a list of this courses plagiarism checks.
     *
     * @param Course $course
     * @return array
     * @throws GuzzleException
     */
    public function getCheckHistory(Course $course): array
    {
        return $this->plagiarismService->getCheckHistory($course);
    }

    /**
     * Update the status for the given match.
     *
     * @param Request $request
     * @return array
     *
     * @throws GuzzleException
     */
    public function updateMatchStatus(Request $request): array
    {
        return $this->plagiarismService->updateMatchStatus(
            $request->input('matchId'),
            $request->input('newStatus'),
            $request->input('comment'),
            app(User::class)->currentUserId()
        );
    }

    /**
     * Returns active (latest) matches of all assignments for the given user
     * @param Course $course
     * @param string $username
     * @return mixed|\stdClass
     * @throws GuzzleException
     */
    public function fetchStudentActiveMatches(Course $course, string $username)
    {
        return $this->plagiarismService->getStudentActiveMatches($course->id, $username);
    }

    /**
     * Returns active (latest) matches of all assignments for the given user
     * @param Course $course
     * @param string $username
     * @return mixed|\stdClass
     * @throws GuzzleException
     */
    public function fetchStudentInactiveMatches(Course $course, string $username)
    {
        return $this->plagiarismService->getStudentInactiveMatches($course->id, $username);
    }
}
