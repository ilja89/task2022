<?php


namespace TTU\Charon\Services;


use Carbon\Carbon;
use TTU\Charon\Exceptions\IncorrectSecretTokenException;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Repositories\GitCallbacksRepository;

class GitCallbackService
{
    /** @var GitCallbacksRepository */
    private $gitCallbacksRepository;


    /**
     * GitCallbackService constructor.
     *
     * @param GitCallbacksRepository $gitCallbacksRepository
     */
    public function __construct(GitCallbacksRepository $gitCallbacksRepository)
    {
        $this->gitCallbacksRepository = $gitCallbacksRepository;
    }

    /**
     * Check if the given token is valid and returns a Git callback.
     *
     * @param  string  $token
     *
     * @return GitCallback
     */
    public function checkGitCallbackForToken($token)
    {
        $gitCallback = $this->gitCallbacksRepository->findByToken($token);
        //$this->checkGitCallback($gitCallback);

        return $gitCallback;
    }

    /**
     * Check the given Git callback. If the secret token isn't correct
     * throw an exception. Also set the response received flag to true.
     *
     * @param  GitCallback $gitCallback
     *
     * @throws IncorrectSecretTokenException
     */
    private function checkGitCallback(GitCallback $gitCallback)
    {
        if ($gitCallback->first_response_time === null) {

            $gitCallback->first_response_time = Carbon::now();
            $gitCallback->save();
        } else if ($gitCallback->first_response_time->diffInMinutes(Carbon::now()) > 3) {
            throw new IncorrectSecretTokenException('incorrect_secret_token', $gitCallback->secret_token);
        }
    }
}
