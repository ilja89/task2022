<?php


namespace TTU\Charon\Repositories;


use Carbon\Carbon;
use TTU\Charon\Models\GitCallback;

/**
 * Class GitCallbacksRepository.
 *
 * @package TTU\Charon\Repositories
 */
class GitCallbacksRepository
{
    /**
     * Save a new Git callback with the given parameters.
     * Also calculates the secret token.
     *
     * @param  string  $fullUrl
     * @param  string  $repo
     * @param  int  $user
     *
     * @return mixed
     */
    public function save($fullUrl, $repo, $user)
    {
        $time = Carbon::now();
        $key = encrypt($repo . '__' . $time->timestamp);
        return GitCallback::create([
            'url' => $fullUrl,
            'repo' => $repo,
            'user' => $user,
            'created_at' => $time,
            'secret_token' => $key
        ]);
    }
}
