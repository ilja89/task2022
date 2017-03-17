<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\GitCallback;
use Zeizig\Moodle\Services\SettingsService;

class GitCallbackController extends Controller
{
    /** @var SettingsService */
    protected $settingsService;

    /**
     * GitCallbackController constructor.
     *
     * @param  Request  $request
     * @param  SettingsService  $settingsService
     */
    public function __construct(Request $request, SettingsService $settingsService)
    {
        parent::__construct($request);
        $this->settingsService = $settingsService;
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

        $testerUrl = $this->settingsService->getSetting('mod_charon', 'tester_url', 'http://neti.ee');
        $params = $this->getTesterRequestParams($gitCallback);
        $client = new Client(['base_uri' => $testerUrl]);
        $client->request('POST', 'test', [ 'json' => $params ]);

        return "SUCCESS";
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
            'callback_url' => $this->request->getUriForPath('/api/tester_callback'),
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
