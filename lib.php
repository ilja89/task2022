<?php

function handleMoodleRequest($route, $method) {
    require_once __DIR__ . '/plugin/bootstrap/autoload.php';
    require_once __DIR__ . '/plugin/bootstrap/helpers.php';
    $app = require __DIR__ . '/plugin/bootstrap/app.php';

    $request = getMoodleRequest($route, $method);
    $response = $app->make(\Illuminate\Contracts\Http\Kernel::class)->handle($request);
    return $response->getOriginalContent();
}

function charon_add_instance($test, $mform) {
    return handleMoodleRequest('charons', 'post');
}

function charon_update_instance($test, $mform) {
    return handleMoodleRequest('charons/update', 'post');
}

function charon_delete_instance($id) {
    return handleMoodleRequest('charons/' . $id . '/delete', 'post');
}

function charon_update_grades($modinstance, $userid = 0, $nullifnone = true) { }

function charon_grade_item_update($modinstance, $grades = NULL) { }

function charon_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('moodle/course:manageactivities', $context)) {
        $url = new moodle_url('/mod/charon/courses/' . $course->id . '/settings', []);
        $settingsnode = navigation_node::create('Charon Settings', $url, navigation_node::TYPE_SETTING,
            null, null, new pix_icon('i/settings', ''));
        $navigation->add_node($settingsnode);

        // TODO: Show link only when Charons exist. Capella too!
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
