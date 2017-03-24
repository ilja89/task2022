<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Services\TesterCommunicationService;

class GitCallbackController extends Controller
{
    /** @var GitCallbacksRepository */
    private $gitCallbacksRepository;

    /** @var TesterCommunicationService */
    private $testerCommunicationService;

    /**
     * GitCallbackController constructor.
     *
     * @param  Request $request
     * @param GitCallbacksRepository $gitCallbacksRepository
     * @param TesterCommunicationService $testerCommunicationService
     */
    public function __construct(
        Request $request,
        GitCallbacksRepository $gitCallbacksRepository,
        TesterCommunicationService $testerCommunicationService
    ) {
        parent::__construct($request);
        $this->gitCallbacksRepository     = $gitCallbacksRepository;
        $this->testerCommunicationService = $testerCommunicationService;
    }

    /**
     * Handle the Git callback. Will generate a key and send it to the tester.
     * This will take all received parameters and add some and send these
     * to the tester.
     * The tester will then run tests and send the results back to Moodle.
     */
    public function index()
    {
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
}
