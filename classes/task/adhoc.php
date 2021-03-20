<?php

namespace mod_charon\task;

use Exception;
use TTU\Charon\Tasks\AdhocTask;

/**
 * Proxy to schedule Moodle adhoc cron tasks
 *
 * @See https://docs.moodle.org/dev/Task_API
 *
 * For manual testing run scheduled adhoc tasks via moodle cli:
 * `php /bitnami/moodle/admin/cli/adhoc_task.php -e -i`
 *
 * Using the -i flag may produce `Undefined variable: adhoclock` error, which you can safely ignore.
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

        $task = $this->getTask($name);

        if (!($task instanceof AdhocTask)) {
            mtrace('Task should implement AdhocTask, skipping');
            return;
        }

        mtrace('Starting task ' . $name);

        $task->execute($payload->arguments);

        mtrace('Finished task ' . $name);
    }

    /**
     * App requires an existing request to function properly.
     * Since this is executed via cron, creating an empty one.
     *
     * @see /lib.php
     * @param string $name
     * @return mixed
     */
    private function getTask(string $name) {
        require_once __DIR__ . '/../../plugin/bootstrap/helpers.php';
        $app = \TTU\Charon\get_app();

        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        try {
            $kernel->handle($request = \Illuminate\Http\Request::capture());
        } catch (Exception $e) {

        }

        return $app->make($name);
    }
}
