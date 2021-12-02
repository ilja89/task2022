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
        GitCallbackService $gitCallbackService
    ) {
        parent::__construct($request);
        $this->gitCallbacksRepository = $gitCallbacksRepository;
        $this->gitCallbackService = $gitCallbackService;
    }

    /**
     * Handle the GitHub callback. Will generate a key and send it to the tester.
     * This will take all received parameters and add some and send these
     * to the tester.
     * The tester will then run tests and send the results back to Moodle.
     *
     * @param GithubCallbackPostRequest $request
     *
     * @return string
     */
    public function gitHubIndexPost(GithubCallbackPostRequest $request)
    {
        Log::info("Git callback with github data validation");

        return $this->gitCallbackService->handleGitHubCallbackPost($request);
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

        return $this->gitCallbackService->handleGitLabCallbackPost($request);
    }
}
