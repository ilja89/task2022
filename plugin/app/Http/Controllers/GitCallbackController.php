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

    public function index()
    {
        $time = Carbon::now();
        $fullUrl = $this->request->fullUrl();
        $key = encrypt($this->request['repo'] . '__' . $time->timestamp);

        $gitCallback = GitCallback::create([
            'url' => $fullUrl,
            'repo' => $this->request['repo'],
            'user' => $this->request['user'],
            'created_at' => $time,
            'secret_token' => $key
        ]);

        $params = [
            'callback_url' => 'neti.ee',
            'secret_token' => $key
        ];
        $params = array_merge($this->request->all(), $params);
        $client = new Client(['base_uri' => 'http://193.40.252.119/']);
        $client->request('POST', 'test', [ 'json' => $params ]);
    }
}
