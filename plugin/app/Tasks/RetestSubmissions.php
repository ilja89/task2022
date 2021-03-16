<?php

namespace TTU\Charon\Tasks;

use Illuminate\Contracts\Logging\Log;
use TTU\Charon\Repositories\UserRepository;

class RetestSubmissions implements AdhocTask
{
    /** @var UserRepository */
    private $userRepository;

    /** @var Log */
    private $logger;

    /**
     * @param UserRepository $userRepository
     * @param Log $logger
     */
    public function __construct(UserRepository $userRepository, Log $logger)
    {
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    public function execute($payload)
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        var_dump("test1");

        $this->userRepository->find(2);
        var_dump("test2");

        // TODO: this logger works differently than the logger via interface. but it still works!
        $this->logger->debug('in executed task', [$this->userRepository->find(2)->username, $payload]);
    }
}
