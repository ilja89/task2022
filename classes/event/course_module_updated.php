<?php

namespace mod_charon;

class course_module_updated
{
    public static function course_module_updated(\core\event\course_module_updated $event)
    {
        if ($event->other['modulename'] === 'charon') {
            require_once __DIR__ . '/../../plugin/bootstrap/autoload.php';
            require_once __DIR__ . '/../../plugin/bootstrap/helpers.php';
            $app = require __DIR__ . '/../../plugin/bootstrap/app.php';
            $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

            $request = getMoodleRequest('post_update/' . $event->other['instanceid'], 'post');

            $kernel->handle($request = $request);
        }
    }

}
