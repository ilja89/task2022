<?php

namespace TTU\Charon\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use TTU\Charon\Models\GitCallback;

class GitCallbackController extends Controller
{
    /** @var Request */
    private $request;

    /**
     * GitCallbackController constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the Git callback. Will generate a key and send it to the tester.
     * This will take all received parameters and add some and send these
     * to the tester.
     * The tester will then run tests and send the results back to Moodle.
     */
    public function index()
    {
        $gitCallback = $this->saveGitCallback();

        $params = $this->getTesterRequestParams($gitCallback);
        $client = new Client(['base_uri' => 'http://193.40.252.119/']);
        $client->request('POST', 'test', [ 'json' => $params ]);
    }

    /**
     * Get the parameters which will be sent to the tester.
     * Takes all the given parameters and add some extra ones.
     *
     * @param  GitCallback  $gitCallback
     *
     * @return array
     */
    private function getTesterRequestParams($gitCallback)
    {
        $params = [
            'callback_url' => 'neti.ee',
            'secret_token' => $gitCallback->secret_token
        ];
        return array_merge($this->request->all(), $params);
    }

    /**
     * Saves the Git Callback to the database. Also generates the secret token.
     *
     * @return GitCallback
     */
    private function saveGitCallback()
    {
        $time = Carbon::now();
        $fullUrl = $this->request->fullUrl();
        $key = encrypt($this->request['repo'] . '__' . $time->timestamp);

        return GitCallback::create([
            'url' => $fullUrl,
            'repo' => $this->request['repo'],
            'user' => $this->request['user'],
            'created_at' => $time,
            'secret_token' => $key
        ]);
    }
}
