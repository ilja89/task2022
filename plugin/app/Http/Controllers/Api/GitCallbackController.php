<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Http\Requests\GitCallbackPostRequest;
use TTU\Charon\Http\Requests\GitCallbackRequest;
use TTU\Charon\Repositories\GitCallbacksRepository;

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

    /**
     * GitCallbackController constructor.
     *
     * @param  Request $request
     * @param GitCallbacksRepository $gitCallbacksRepository
     */
    public function __construct(
        Request $request,
        GitCallbacksRepository $gitCallbacksRepository
    ) {
        parent::__construct($request);
        $this->gitCallbacksRepository     = $gitCallbacksRepository;
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
        $username = $request->input('user_username');
        $gitCallback = $this->gitCallbacksRepository->save(
            $request->fullUrl(),
            $repo,
            $username
        );

        $params = ['repo' => $repo, 'user' => $username, 'extra' => $request->all()];

        event(new GitCallbackReceived(
            $gitCallback,
            $request->getUriForPath('/api/tester_callback'),
            $params
        ));

        return "SUCCESS";
    }
}
