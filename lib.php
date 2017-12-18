<?php

function charon_add_instance($test, $mform) {
    require_once __DIR__ . '/plugin/bootstrap/helpers.php';

    return TTU\Charon\handleMoodleRequest('charons', 'post');
}

function charon_update_instance($test, $mform) {
    require_once __DIR__ . '/plugin/bootstrap/helpers.php';

    return TTU\Charon\handleMoodleRequest('charons/update', 'post');
}

function charon_delete_instance($id) {
    require_once __DIR__ . '/plugin/bootstrap/helpers.php';

    $app = TTU\Charon\getApp();

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    try {
        // Need to make a request before we can use app->make to get the controller
        // Cannot use routes because this deleting is initiated by the cron job (No server, url parameters etc)
        // TODO: Make this better!
        $kernel->handle($request = \Illuminate\Http\Request::capture());
    } catch (Exception $e) { }

    $controller = $app->make(\TTU\Charon\Http\Controllers\InstanceController::class);

    return $controller->destroy($id);
}

function charon_update_grades($modinstance, $userid = 0, $nullifnone = true) { }

function charon_grade_item_update($modinstance, $grades = NULL) { }

function charon_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('moodle/course:manageactivities', $context)) {
        $url = new moodle_url('/mod/charon/courses/' . $course->id . '/settings', []);
        $settingsnode = navigation_node::create('Submission Settings', $url, navigation_node::TYPE_SETTING,
            null, null, new pix_icon('i/settings', ''));
        $navigation->add_node($settingsnode);

        // TODO: Show link only when Charons exist. Capella too!
        $url = new moodle_url('/mod/charon/courses/' . $course->id . '/popup');
        $settingsnode = navigation_node::create('Submission Popup', $url, navigation_node::TYPE_SETTING,
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
