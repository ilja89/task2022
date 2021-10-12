<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default errors
    |--------------------------------------------------------------------------
    |
    */
    'forbidden' => [
        'title'  => 'The request is unauthorized.',
        'detail' => 'You are not authorised to perform this action.'
    ],

    'not_found' => [
        'title'  => 'The requested resource could not be found.',
        'detail' => 'The resource you were looking for was not found.'
    ],

    'charon_not_found' => [
        'title' => 'The requested Charon instance was not found.',
        'detail' => 'The Charon with the given ID: %d could not be found.'
    ],

    'charon_course_module_not_found' => [
        'title' => 'The requested course module is not a Charon instance.',
        'detail' => 'The course module with the given ID: %d does not exist or is not an instance of Charon.',
    ],

    'no_secret_token_found' => [
        'title' => 'Secret token not included in request.',
        'detail' => 'The secret token was not included in the given request.',
    ],

    'incorrect_secret_token' => [
        'title' => 'Secret token included in the request was not correct.',
        'detail' => 'The secret token in the request was not correct: %s.',
    ],

    'user_cannot_access_course_module' => [
        'title' => 'Cannot access the given course module.',
        'detail' => 'The course module with ID: %d cannot be accessed by user with ID: %d.',
    ],

    'course_management_permission_denied' => [
        'title' => 'Permission denied for editing the given course.',
        'detail' => 'The user with ID: %d from IP: %s does not have permission to manage the course with ID: %d.',
    ],

    'result_points_are_required' => [
        'title' => 'Result points are required.',
        'detail' => 'All result points are required when saving a submission.',
    ],

    'template_path_are_required' => [
        'title' => 'Template path are required.',
        'detail' => 'Path are required when saving template.',
    ],

    'same_path' => [
        'title' => 'Two templates with the same path.',
        'detail' => 'Templates with same name: %s cannot be added.',
    ],

    'submission_git_callback_is_required' => [
        'title' => 'Submission is not linked to a Git callback.',
        'detail' => 'Submission requires a Git callback so it can be retested.',
    ],
    /*
    |--------------------------------------------------------------------------
    | Defense registration errors
    |--------------------------------------------------------------------------
    |
    */

    'teacher_is_busy' => [
        'title' => 'Your teacher isn\'t vacant at given time.',
        'detail' => 'Please choose another time or if possible, another teacher.',
    ],

    'no_teacher_available' => [
        'title' => 'No available teachers were found!',
        'detail' => '',
    ],

    'user_in_db' => [
        'title' => 'You cannot register twice for one exercise.',
        'detail' => 'If you want to choose another time, then you should delete your previous time (My registrations button)',
    ],

    'not_enough_time' => [
        'title' => 'Not enough time left in lab queue.',
        'detail' => 'The lab you tried to register your defense to ' .
            'does not have enough time left to defend this charon.',
    ],

    'invalid_setup' => [
        'title' => 'Alert teachers that lab configuration was invalid!',
        'detail' => '',
    ],

    'invalid_chosen_time' => [
        'title' => 'Invalid chosen time!',
        'detail' => '',
    ],

    'duplicate' => [
        'title' => 'You already have an registration for this time!',
        'detail' => '',
    ],

];
