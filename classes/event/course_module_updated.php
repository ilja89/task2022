<?php

namespace mod_charon;

use TTU\Charon\Http\Controllers\InstanceController;

class course_module_updated
{
    public static function course_module_updated(\core\event\course_module_updated $event)
    {
        if ($event->other['modulename'] === 'charon') {
            require_once __DIR__ . '/../../plugin/bootstrap/autoload.php';
            $app = require __DIR__ . '/../../plugin/bootstrap/app.php';
            $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
            $kernel->handle($request = \Illuminate\Http\Request::capture());

            /** @var InstanceController $instanceController */
            $instanceController = $app->make(InstanceController::class);

            $instanceController->postCourseModuleUpdated(
                $event->other['instanceid']
            );
        }
    }

}
