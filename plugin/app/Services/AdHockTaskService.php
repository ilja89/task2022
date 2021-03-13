<?php

namespace TTU\Charon\Services;

use Illuminate\Contracts\Logging\Log;
use TTU\Charon\Repositories\UserRepository;

/**
 * Class DeadlineService.
 *
 * @package TTU\Charon\Services
 */
class AdHockTaskService
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

    public function execute(array $payload)
    {
        // TODO: this logger works differently than the logger via interface. but it still works!
        $this->logger->debug('in executed task', [/*$this->userRepository->find(2)->username, */$payload]);
    }
}
