<?php

use Illuminate\Http\Request;

function getApp() {
    require_once __DIR__ . '/plugin/bootstrap/autoload.php';
    $app = require __DIR__ . '/plugin/bootstrap/app.php';
    // Need to make a small request first because Laravel can't initialize the Request parameter
    // for InstanceController otherwise.
    // TODO: Should refactor somehow.
    $app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(Request::capture());
    return $app;
}

function charon_add_instance($test, $mform) {
    $app = getApp();
    return $app->make(TTU\Charon\Http\Controllers\InstanceController::class)->store();
}

function charon_update_instance($test, $mform) {
    $app = getApp();
    return $app->make(TTU\Charon\Http\Controllers\InstanceController::class)->update();
}

function charon_delete_instance($id) {
    $app = getApp();
    $instanceController = $app->make(TTU\Charon\Http\Controllers\InstanceController::class);
    return $instanceController->destroy($id);
}

function charon_update_grades($modinstance, $userid = 0, $nullifnone = true) { }

function charon_grade_item_update($modinstance, $grades = NULL) { }

function charon_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('moodle/course:manageactivities', $context)) {
        $url = new moodle_url('/mod/charon/courses/' . $course->id . '/settings', []);
        $settingsnode = navigation_node::create('Charon Settings', $url, navigation_node::TYPE_SETTING,
            null, null, new pix_icon('i/settings', ''));
        $navigation->add_node($settingsnode);

        $url = new moodle_url('/mod/charon/courses/' . $course->id . '/popup');
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
