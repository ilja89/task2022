<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\GitCallbackPostRequest;
use TTU\Charon\Http\Requests\GitCallbackRequest;
use TTU\Charon\Http\Requests\GithubCallbackPostRequest;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Repositories\UserRepository;
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

    /** @var GitCallbackService */
    private $gitCallbackService;

    /** @var UserRepository */
    private $userRepository;

    /**
     * GitCallbackController constructor.
     *
     * @param Request $request
     * @param GitCallbacksRepository $gitCallbacksRepository
     * @param GitCallbackService $gitCallbackService
     */
    public function __construct(
        Request $request,
        GitCallbacksRepository $gitCallbacksRepository,
        GitCallbackService $gitCallbackService,
        UserRepository $userRepository
    ) {
        parent::__construct($request);
        $this->gitCallbacksRepository = $gitCallbacksRepository;
        $this->gitCallbackService = $gitCallbackService;
        $this->userRepository = $userRepository;
    }

    public function gitHubPostIndex(GithubCallbackPostRequest $request)
    {
        Log::info("Git callback with github data validation");
        $repo = $request->input('repository')['ssh_url'];
        $userEmail = $request->input('repository')['owner']['email'];
        $callbackUrl = $request->getUriForPath('/api/tester_callback');
        $fullUrl = $request->fullUrl();
        $params = [];

        Log::debug('Initial user has email: "' . $userEmail . '"');

        $user = $this->userRepository->findByEmail($userEmail);

        Log::debug('Initial user' , [$user]);

        if (!$user) {
            Log::debug('User not found with email' , [$userEmail]);
            return 'NO USER';
        }

        $params['email'] = $userEmail;

        $username = str_replace("@ttu.ee", '', $user->username);

        return $this->gitCallbackService->saveFromCallback($username, $fullUrl, $repo, $callbackUrl,
            $params, $request->input('commits', []));
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
        Log::info("Git callback with gitlab data validation");
        $repo = $request->input('repository')['git_ssh_url'];
        $initialUser = $request->input('user_username');
        $callbackUrl = $request->getUriForPath('/api/tester_callback');
        $fullUrl = $request->fullUrl();
        $params = [];

        Log::debug('Initial user has username: "' . $initialUser . '"');

        if ($request->input('commits')) {
            $params['email'] = $request->input('commits.0.author.email');
        }

        return $this->gitCallbackService->saveFromCallback($initialUser, $fullUrl, $repo, $callbackUrl,
        $params, $request->input('commits', []));
    }
}
