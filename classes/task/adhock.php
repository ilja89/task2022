<?php

namespace mod_charon\task;

use Exception;
use Illuminate\Contracts\Logging\Log;
use TTU\Charon\Services\AdHockTaskService;

class adhock extends \core\task\adhoc_task
{
    public function execute()
    {
        $payload = $this->get_custom_data();

        if (!isset($payload['task_name'])) {
            return;
        }

        $task = $payload['task_name'];

        require_once __DIR__ . '/../../plugin/bootstrap/autoload.php';
        require_once __DIR__ . '/../../plugin/bootstrap/helpers.php';
        $app = require __DIR__ . '/../../plugin/bootstrap/app.php';

        /** @var Log $logger */
        $logger = $app->make(Log::class);

        $logger->debug('Starting task ' . $task);

        try {
            // TODO: use task name here, typehint via interface
            /** @var AdHockTaskService $service */
            $service = $app->make($task);

            // TODO: check for interface

            $service->execute($payload);

            $logger->debug('Finished task ' . $task);
        } catch (Exception $exception) {
            $logger->debug('Task ' . $task . ' failed with:' . $exception->getMessage(), $exception->getTrace());
        }
    }
}
