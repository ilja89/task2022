<?php

namespace TTU\Charon\Facades;

/**
 * Tasks scheduled here are executed at \mod_charon\task\adhoc.
 *
 * @see https://docs.moodle.org/dev/Task_AP
 */
class MoodleCron
{
    /**
     * @param string $class Should implement \TTU\Charon\Tasks\AdhocTask
     * @param array $arguments Arguments passed to $task->execute()
     * @param int $delay delay in seconds
     */
    public function enqueue(string $class, array $arguments = [], int $delay = 0) {
        $task = new \mod_charon\task\adhoc();

        $task->set_custom_data([
            'task' => $class,
            'arguments' => $arguments
        ]);

        $task->set_component('mod_charon');

        if ($delay > 0) {
            $task->set_next_run_time(time() + $delay);
        }

        \core\task\manager::queue_adhoc_task($task);
    }
}
