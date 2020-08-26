<?php

namespace mod_charon;

use Illuminate\Support\Facades\Log;
use function TTU\Charon\getMoodleRequest;

class course_module_created
{
    public static function course_module_created(\core\event\course_module_created $event)
    {
        if ($event->other['modulename'] === 'charon') {

            Log::info("Create event:", [$event]);

            global $CHARON_CREATED;
            $CHARON_CREATED = true;

            require_once __DIR__ . '/../../plugin/bootstrap/autoload.php';
            require_once __DIR__ . '/../../plugin/bootstrap/helpers.php';
            $app = require __DIR__ . '/../../plugin/bootstrap/app.php';
            $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

            $request = getMoodleRequest('post_create/' . $event->other['instanceid'], 'post');

            $kernel->handle($request = $request);
        }
    }
}
