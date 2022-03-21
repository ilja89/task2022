<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\PlagiarismCheck;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\PlagiarismRepository;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Services\UserService;

/**
 * Class PlagiarismService
 *
 * @package TTU\Charon\Services
 */
class PlagiarismService
{
    /** @var PlagiarismCommunicationService */
    private $plagiarismCommunicationService;

    /** @var CharonRepository */
    private $charonRepository;

    /** @var PlagiarismRepository */
    private $plagiarismRepository;

    /** @var UserService */
    private $userService;

    /** @var SubmissionService */
    private $submissionService;

    /**
     * PlagiarismService constructor.
     *
     * @param PlagiarismCommunicationService $plagiarismCommunicationService
     * @param CharonRepository $charonRepository
     * @param PlagiarismRepository $plagiarismRepository
     */
    public function __construct(
        PlagiarismCommunicationService $plagiarismCommunicationService,
        CharonRepository $charonRepository,
        UserService $userService,
        SubmissionService $submissionService,
        PlagiarismRepository $plagiarismRepository
    )
    {
        $this->plagiarismCommunicationService = $plagiarismCommunicationService;
        $this->charonRepository = $charonRepository;
        $this->plagiarismRepository = $plagiarismRepository;
        $this->userService = $userService;
        $this->submissionService = $submissionService;
    }


    /**
     * Create a plagiarism checksuite for the given Charon and save the
     * checksuite id to the Charon.
     *
     * @param Charon $charon
     * @param string[] $plagiarismServices
     * @param array $resourceProviders
     * @param string $includes
     *
     * @return Charon
     *
     * @throws GuzzleException
     */
    public function createChecksuiteForCharon(Charon $charon, $plagiarismServices, $resourceProviders, $includes)
    {
        $response = $this->plagiarismCommunicationService->createChecksuite(
            $charon,
            $plagiarismServices,
            $resourceProviders,
            $includes
        );

        $charon = $this->charonRepository->updatePlagiarismChecksuiteId(
            $charon,
            $response->id
        );

        return $charon;
    }

    /**
     * Run the checksuite for the given Charon and refresh its latest check id.
     *
     * @param Charon $charon
     *
     * @return Charon
     *
     * @throws GuzzleException
     */
    public function runChecksuite(Charon $charon)
    {
        $this->plagiarismCommunicationService->runChecksuite($charon->plagiarism_checksuite_id);
        $charon = $this->refreshLatestCheckId($charon);

        return $charon;
    }

    /**
     * Run the check for the given Charon and refresh its status.
     *
     * @param Charon $charon
     * @param Course $course
     * @param Request $request
     * @return array
     *
     * @throws GuzzleException
     */
    public function runCheck(Charon $charon, Course $course, Request $request): array
    {
        $check = $this->plagiarismRepository->addPlagiarismCheck($charon->id, app(User::class)->currentUserId(), "Trying to get connection to Plagiarism API");
        $response = $this->plagiarismCommunicationService->runCheck($charon->project_folder, $course->shortname, $request->getUriForPath("/api/plagiarism_callback/" . $check->id));

        $check->updated_at = Carbon::now();
        $check->status = $response;
        $check->save();
        return [
            "charonName" => $charon->name,
            "created_at" => $check->created_at,
            "updated_at" => $check->updated_at,
            "status" => $check->status,
            "checkId" => $check->id,
            "author" => $check->user->firstname . ' ' . $check->user->lastname
        ];
    }

    /**
     * Refresh the latest check id for the given Charon. If the Charon has a
     * checksuite, its info will be fetched from the plagiarism service and will
     * be parsed to find the latest check's id. This will be saved to the Charon
     * instance.
     *
     * @param Charon $charon
     *
     * @return Charon
     *
     * @throws GuzzleException
     */
    public function refreshLatestCheckId(Charon $charon)
    {
        if (!$charon->plagiarism_checksuite_id) {
            return $charon;
        }

        $checksuite = $this->plagiarismCommunicationService->getChecksuiteDetails(
            $charon->plagiarism_checksuite_id
        );

        $charon->plagiarism_latest_check_id = $checksuite->checks[0]->id;
        $charon->save();

        return $charon;
    }

    /**
     * Get the similarities for the given Charon from the plagiarism service.
     *
     * Returns a list where each element contains information for one service.
     * If the service check was unsuccessful, or is pending, will just include
     * the status information. Otherwise, if the check was successful, will
     * include its similarities.
     *
     * @param Charon $charon
     *
     * @return array - the similarities for each service (moss, jplag) if we
     *      have that data, otherwise show status (pending, error).
     *
     * @throws GuzzleException
     */
    public function getLatestSimilarities(Charon $charon)
    {
        $check = $this->plagiarismCommunicationService->getCheckDetails(
            $charon->plagiarism_latest_check_id
        );

        $similarities = [];
        // Map the statuses of the services to similarities
        $serviceTrackings = $check->check->plagiarismServiceTrackings;
        foreach ($serviceTrackings as $serviceTracking) {
            if ($serviceTracking->state === 'PLAGIARISM_SERVICE_SUCCESS') {
                // If the status for this service is successful, we can use the
                // similarity and show its data.
                $similarity = collect($check->similarities)
                    ->first(function ($value) use ($serviceTracking) {
                        return $value->name === $serviceTracking->name;
                    });
                $similarity->state = $serviceTracking->state;
            } else {
                // If the status is not successful (pending, error), then we do
                // not have any similarities to show. But we can append the
                // service status info, so that we can show some message on the
                // front-end.
                $similarity = $serviceTracking;
            }

            $similarities[] = $similarity;
        }

        return $similarities;
    }

    /**
     * Get the matches for the given Charon from the plagiarism service.
     * And associate matches submissions and users.
     *
     * @param Charon $charon
     * @param Course $course
     *
     * @return array
     * @throws GuzzleException
     */
    public function getMatches(Charon $charon, Course $course): array
    {
        $matches = $this->plagiarismCommunicationService->getMatches($charon->project_folder, $course->shortname);
        $result = [];
        foreach ($matches as $match) {
            $submission = $this->submissionService->findSubmissionByHash($match['commit_hash']);
            $otherSubmission = $this->submissionService->findSubmissionByHash($match['other_commit_hash']);
            if ($submission and $otherSubmission) {
                $match['user_id'] = $submission->user_id;
                $match['other_user_id'] = $otherSubmission->user_id;
                $match['submission_id'] = $submission->id;
                $match['other_submission_id'] = $otherSubmission->id;
            } else  {
                $user = $this->userService->findUserByUniid($match['uniid']);
                $otherUser = $this->userService->findUserByUniid($match['other_uniid']);
                if ($user and $otherUser) {
                    $match['user_id'] = $user->id;
                    $match['other_user_id'] = $otherUser->id;
                } else {
                    $match['user_id'] = null;
                    $match['other_user_id'] = null;
                }
                $match['submission_id'] = null;
                $match['other_submission_id'] = null;
            }
            $result[] = $match;
        }

        return $result;
    }

    /**
     * Get a list of checks for given course.
     *
     * @param Course $course
     *
     * @return array
     */
    public function getCheckHistory(Course $course): array
    {
        $checks = $this->plagiarismRepository->getChecksByCourseId($course->id);

        foreach ($checks as $check) {
            $check->author = $check->firstname . ' ' . $check->lastname;
            unset($check->firstname);
            unset($check->lastname);
        }

        return $checks;
    }

    /**
     * Update status of the plagiarism check.
     *
     * @param PlagiarismCheck $check
     * @param array $response
     */
    public function updateCheck(PlagiarismCheck $check, array $response): void
    {
        $check->updated_at = Carbon::now();
        $check->status = $response['status'];
        $check->save();
    }
}
