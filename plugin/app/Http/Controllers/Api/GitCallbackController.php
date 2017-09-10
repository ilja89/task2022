<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
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
     */
    public function index()
    {
        $this->validate($this->request, [
            'repo' => 'required',
            'user' => 'required',
        ]);

        $gitCallback = $this->gitCallbacksRepository->save(
            $this->request->fullUrl(),
            $this->request->input('repo'),
            $this->request->input('user')
        );

        event(new GitCallbackReceived(
            $gitCallback,
            $this->request->getUriForPath('/api/tester_callback'),
            $this->request->all()
        ));

        return "SUCCESS";
    }

    public function indexPost()
    {
        $this->validate($this->request, [
            'repository' => 'required',
            'user_username' => 'required',
        ]);

        $repo = $this->request->input('repository')['git_ssh_url'];
        $username = $this->request->input('user_username');
        $gitCallback = $this->gitCallbacksRepository->save(
            $this->request->fullUrl(),
            $repo,
            $username
        );

        $params = ['repo' => $repo, 'user' => $username, 'extra' => $this->request->all()];

        event(new GitCallbackReceived(
            $gitCallback,
            $this->request->getUriForPath('/api/tester_callback'),
            $params
        ));

        return "SUCCESS";
    }

    /**
     * Trigger retesting the student's solution to a given task.
     *
     * @param Charon $charon
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function retest(Charon $charon)
    {
        // TODO: Make this work


        return response()->json([
            'status' => 500,
            'data' => [
                'message' => 'Retesting is not supported yet!',
            ],
        ], 500);
    }
}
