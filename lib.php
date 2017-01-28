<?php

function charon_add_instance($test, $mform) {
    require_once __DIR__ . '/plugin/bootstrap/autoload.php';
    $app = require __DIR__ . '/plugin/bootstrap/app.php';
    return app(TTU\Charon\Http\Controllers\InstanceController::class)->store();
}

function charon_update_instance($test, $mform) {
    require_once __DIR__ . '/plugin/bootstrap/autoload.php';
    $app = require __DIR__ . '/plugin/bootstrap/app.php';
    return app(TTU\Charon\Http\Controllers\InstanceController::class)->update();
}

function charon_delete_instance($id) {
    require_once __DIR__ . '/plugin/bootstrap/autoload.php';
    $app = require __DIR__ . '/plugin/bootstrap/app.php';
    // Need to make a small request first because Laravel can't initialize the Request parameter
    // for InstanceController otherwise.
    // TODO: Should refactor somehow.
    $kernel = app(Illuminate\Contracts\Http\Kernel::class);
    $kernel->handle($request = Illuminate\Http\Request::capture());

    $instanceController = app(TTU\Charon\Http\Controllers\InstanceController::class);
    return $instanceController->destroy($id);
}

function charon_update_grades($modinstance, $userid = 0, $nullifnone = true) { }

function charon_grade_item_update($modinstance, $grades = NULL) { }

function charon_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('moodle/course:manageactivities', $context)) {
        $url = new moodle_url('/mod/charon/courses/' . $course->id . '/settings', []);
        $settingsnode = navigation_node::create('Charon settings', $url, navigation_node::TYPE_SETTING,
            null, null, new pix_icon('i/settings', ''));
        $navigation->add_node($settingsnode);

        $url = new moodle_url('/mod/charon/popup', ['course_id' => $course->id]);
        $settingsnode = navigation_node::create('Charon Popup', $url, navigation_node::TYPE_SETTING,
            null, null, new pix_icon('i/settings', ''));
        $navigation->add_node($settingsnode);
    }
}

function charon_supports($feature) {

    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        default:
            return null;
    }
}
