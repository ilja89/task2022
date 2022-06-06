<?php

namespace TTU\Charon\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\GitCallbackPostRequest;
use TTU\Charon\Http\Requests\GitCallbackRequest;
use TTU\Charon\Models\CourseSettings;
use TTU\Charon\Repositories\CharonChainRepository;
use TTU\Charon\Repositories\CharonRepository;
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

    /** @var CharonChainRepository */
    private $charonChainRepository;

    /** @var CharonRepository */
    private $charonRepository;

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
        GitCallbackService $gitCallbackService,
        CharonChainRepository $charonChainRepository,
        CharonRepository $charonRepository
    ) {
        parent::__construct($request);
        $this->gitCallbacksRepository = $gitCallbacksRepository;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->gitCallbackService = $gitCallbackService;
        $this->charonChainRepository = $charonChainRepository;
        $this->charonRepository = $charonRepository;
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

        if (is_null($course)) {
            Log::warning('No course discovered, maybe git repo address is not in valid format.');
            $this->gitCallbackService->saveCallbackForUser($initialUser, $fullUrl, $repo, $callbackUrl, $params);
            return 'NO COURSE';
        }

        Log::debug('Found course: "' . $course->shortname . '" with ID ' . $course->id);

        /** @var CourseSettings $settings */
        $settings = $this->courseSettingsRepository->getCourseSettingsByCourseId($course->id);

        $params['gitTestRepo'] = '';
        $params['testingPlatform'] = '';

        if ($settings && $settings->testerType) {
            Log::info("TesterType found from CourseSettings: '" . $settings->testerType->name . "'");
            $params['testingPlatform'] = $settings->testerType->name;
        }

        $modifiedFiles = $this->gitCallbackService->getModifiedFiles($request->input('commits', []));
        Log::debug('Found modified files: ', $modifiedFiles);

        $charons = $this->gitCallbackService->findCharons($modifiedFiles, $course->id);

        if (empty($charons)) {
            Log::warning('No matching Charons were found. Forwarding to tester.');
            $this->gitCallbackService->saveCallbackForUser($initialUser, $fullUrl, $repo, $callbackUrl, $params);
            return 'NO MATCHING CHARONS';
        }

        foreach ($charons as $charon) {
            try {
                if (!is_null($charon->charon_chain)) {
                    $chains = $this->charonChainRepository->getAllChains($charon);
                    $subcharons = collect([]);
                    foreach($chains as $chain) {
                        $subcharons->add($this->charonRepository->getCharonById($chain->charon_id));
                    }
                    $this->gitCallbackService->forwardToTester($charon, $settings, $initialUser, $fullUrl, $repo, $callbackUrl, $params, $subcharons);
                } else {
                    $this->gitCallbackService->forwardToTester($charon, $settings, $initialUser, $fullUrl, $repo, $callbackUrl, $params, null);
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }


        }

        return 'SUCCESS';
    }
}
