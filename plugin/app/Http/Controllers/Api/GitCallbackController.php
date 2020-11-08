<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\GitCallbackPostRequest;
use TTU\Charon\Http\Requests\GitCallbackRequest;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Services\GitCallbackService;

/**
 * Class GitCallbackController.
 * Receives Git callbacks, saves them and notifies the tester of them.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class GitCallbackController extends Controller
{
    /** @var GitCallbacksRepository */
    private $gitCallbacksRepository;

    /** @var CourseSettingsRepository */
    private $courseSettingsRepository;

    /** @var GitCallbackService */
    private $gitCallbackService;

    /**
     * GitCallbackController constructor.
     *
     * @param Request $request
     * @param GitCallbacksRepository $gitCallbacksRepository
     * @param CourseSettingsRepository $courseSettingsRepository
     * @param GitCallbackService $gitCallbackService
     */
    public function __construct(
        Request $request,
        GitCallbacksRepository $gitCallbacksRepository,
        CourseSettingsRepository $courseSettingsRepository,
        GitCallbackService $gitCallbackService
    )
    {
        parent::__construct($request);
        $this->gitCallbacksRepository = $gitCallbacksRepository;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->gitCallbackService = $gitCallbackService;
    }

    /**
     * Handle the Git callback. Will generate a key and send it to the tester.
     * This will take all received parameters and add some and send these
     * to the tester.
     * The tester will then run tests and send the results back to Moodle.
     *
     * @param GitCallbackRequest $request
     *
     * @return string
     */
    public function index(GitCallbackRequest $request)
    {
        $gitCallback = $this->gitCallbacksRepository->save(
            $request->fullUrl(),
            $request->input('repo'),
            $request->input('user')
        );

        event(new GitCallbackReceived(
            $gitCallback,
            $request->getUriForPath('/api/tester_callback'),
            $request->all()
        ));

        return "SUCCESS";
    }

    /**
     * Handle the Git callback. Will generate a key and send it to the tester.
     * This will take all received parameters and add some and send these
     * to the tester.
     * The tester will then run tests and send the results back to Moodle.
     * This is for the new POST request.
     *
     * @param GitCallbackPostRequest $request
     *
     * @return string
     */
    public function indexPost(GitCallbackPostRequest $request)
    {
        $repo = $request->input('repository')['git_ssh_url'];
        $initialUser = $request->input('user_username');
        $callbackUrl = $request->getUriForPath('/api/tester_callback');
        $fullUrl = $request->fullUrl();
        $params = [];

        Log::debug('Initial user has username: "' . $initialUser . '"');

        if ($request->input('commits')) {
            $params['email'] = $request->input('commits.0.author.email');
        }

        $course = $this->gitCallbackService->getCourse($repo);

        if (!$course) {
            Log::warning('No course discovered, maybe git repo address is not in valid format.');

            $this->gitCallbackService->saveCallbackForUser($initialUser, $fullUrl, $repo, $callbackUrl, $params);

            return "SUCCESS";
        }

        Log::debug('Found course: "' . $course->shortname . '" with ID ' . $course->id);

        $params['gitTestRepo'] = $this->courseSettingsRepository->getCourseSettingsByCourseId($course->id)->unittests_git;
        $params['testingPlatform'] = $this->courseSettingsRepository->getCourseSettingsByCourseId($course->id)->testerType->name;

        $modifiedFiles = $this->gitCallbackService->getModifiedFiles($request->get('commits'));
        $charons = $this->gitCallbackService->findCharons($modifiedFiles, $course->id);

        if (empty($charons)) {
            Log::warning('This charon is not a group work. Forwarding to tester.');

            $this->gitCallbackService->saveCallbackForUser($initialUser, $fullUrl, $repo, $callbackUrl, $params);

            return "SUCCESS";
        }

        foreach ($charons as $charon) {
            Log::debug("Found charon with id: " . $charon->id);

            if ($charon['tester_extra'] != null) {
                $params['dockerExtra'] = $charon['tester_extra'];
            }
            if ($charon['docker_test_root'] != null) {
                $params['dockerTestRoot'] = $charon['docker_test_root'];
            }
            if ($charon['docker_content_root'] != null) {
                $params['dockerContentRoot'] = $charon['docker_content_root'];
            }
            if ($charon['docker_timeout'] != null) {
                $params['dockerTimeout'] = $charon['docker_timeout'];
            }
            if ($charon['tester_type_code'] != null) {
                $params['testingPlatform'] = $charon->testerType->name;
            }
            if ($charon['system_extra'] != null) {
                $params['systemExtra'] = explode(',', $charon['system_extra']);
            }

            if ($charon->grouping_id == null) {
                Log::info('This charon is not a group work or is broken. Forwarding to tester.');

                $this->gitCallbackService->saveCallbackForUser($initialUser, $fullUrl, $repo, $callbackUrl, $params);

                return "SUCCESS";
            }

            Log::debug('Charon has grouping id ' . $charon->grouping_id);

            $usernames = $this->gitCallbackService->getGroupUsers($charon->grouping_id, $initialUser);

            if (empty($usernames)) {
                Log::warning('Unable to find users in group. Forwarding to tester.');

                $this->gitCallbackService->saveCallbackForUser($initialUser, $fullUrl, $repo, $callbackUrl, $params);

                return "SUCCESS";
            }

            foreach ($usernames as $username) {
                $this->gitCallbackService->saveCallbackForUser($username, $fullUrl, $repo, $callbackUrl, $params);
            }
        }

        return "SUCCESS";
    }
}
