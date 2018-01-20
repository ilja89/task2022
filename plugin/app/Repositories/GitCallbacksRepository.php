<?php


namespace TTU\Charon\Repositories;


use Carbon\Carbon;
use TTU\Charon\Exceptions\IncorrectSecretTokenException;
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
     * @return GitCallback
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

    /**
     * Find a Git callback by the given secret token. If no token is given
     * or no GitCallbackRequest is found, an IncorrectSecretTokenException will be
     * thrown.
     *
     * @param string $secretToken
     *
     * @return GitCallback
     * @throws IncorrectSecretTokenException
     */
    public function findByToken($secretToken)
    {
        if ($secretToken === null) {
            throw new IncorrectSecretTokenException('no_secret_token_found');
        }

        $gitCallback = GitCallback::where('secret_token', $secretToken)
                                  ->get();

        if ($gitCallback->isEmpty()) {
            throw new IncorrectSecretTokenException('incorrect_secret_token', $secretToken);
        }

        return $gitCallback->first();
    }
}
