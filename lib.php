<?php

require_once __DIR__ . '/plugin/bootstrap/autoload.php';
$app = require __DIR__ . '/plugin/bootstrap/app.php';

function charon_add_instance($test, $mform) {
    return app(TTU\Charon\Http\Controllers\InstanceController::class)->store();
}

function charon_update_instance($test, $mform) {
    return app(TTU\Charon\Http\Controllers\InstanceController::class)->update();
}

function charon_delete_instance($id) {
    $app = require __DIR__ . '/plugin/bootstrap/app.php';
    // Need to make a small request first because Laravel can't initialize the Request parameter
    // for InstanceController otherwise.
    // TODO: Should refactor somehow.
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $kernel->handle($request = Illuminate\Http\Request::capture());

    $instanceController = $app->make(TTU\Charon\Http\Controllers\InstanceController::class);
    return $instanceController->destroy($id);
}

function charon_update_grades($modinstance, $userid = 0, $nullifnone = true) {

}

function charon_grade_item_update($modinstance, $grades = NULL) {
}

function charon_supports($feature) {

    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        default:
            return null;
    }
}
