<?php

namespace mod_charon\task;

use Exception;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Support\Facades\Config;
use TTU\Charon\Tasks\AdhocTask;

/**
 * For manual testing run scheduled ad-hock tasks via moodle cli:
 * php /bitnami/moodle/admin/cli/adhoc_task.php -e -i
 */
class adhoc extends \core\task\adhoc_task
{
    public function execute()
    {
        $payload = $this->get_custom_data();

        if (!is_object($payload) || !isset($payload->task)) {
            return;
        }

        $name = $payload->task;

        require_once __DIR__ . '/../../plugin/bootstrap/autoload.php';
        $app = require __DIR__ . '/../../plugin/bootstrap/app.php';
        $app->boot();

        // TODO: Unable to get the laravel app working from this directory. Try calling a command?

        /** @var Log $logger */
        $logger = $app->make(Log::class);

        $logger->debug('Starting task ' . $name);

        try {
            /** @var AdhocTask $service */
            $task = $app->make($name);

            if (!($task instanceof AdhocTask)) {
                $logger->debug('Task should implement AdhocTask, skipping');
                return;
            }

            $task->execute($payload->data);

            $logger->debug('Finished task ' . $name);
        } catch (Exception $exception) {
            $logger->debug('Task ' . $name . ' failed with:' . $exception->getMessage(), $exception->getTrace());
        }
    }
}
