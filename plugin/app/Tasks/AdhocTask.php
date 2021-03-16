<?php

namespace TTU\Charon\Tasks;

/**
 * Non-repeating task executed on Moodle cron
 */
interface AdhocTask
{
    /**
     * @param $payload mixed Deserialized from database, suggest checking type before usage.
     */
    public function execute($payload);
}
