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

    'template_paths_need_to_be_unique' => [
        'title' => 'Template paths need to be unique.',
        'detail' => 'Cannot create templates that have the same paths.',
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

    'charon_registration_exists' => [
        'title' => 'You have already active registration.',
        'detail' => 'One charon can be registered for defense only once.',
    ],

    'charon_defended' => [
        'title' => 'You have already defended.',
        'detail' => 'You cannot defend same charon second time.',
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

    'no_submission' => [
        'title' => 'No eligible submission was found for registration.',
        'detail' => '',
    ],

    'invalid_lab_teacher' => [
        'title' => 'Only lab teacher is able to change the registration.',
        'detail' => 'Registration can be changed only by teacher' .
            ' who is lab teacher of lab in which registration belongs to.',
    ],

    'not_in_group' => [
        'title' => 'Only student in group can register for this registration.',
        'detail' => 'To register for this registration, student should be in group, ' .
            'which is allowed to register for this defense registration.',
    ],

    'group_submission_needed' => [
        'title' => 'Needed group submission for registration.',
        'detail' => 'Submission should be group submission to register for this defense registration.',
    ],

    'group_submission_not_allowed' => [
        'title' => 'Group submission not allowed for registration.',
        'detail' => 'Submission can\'t be group submission to register for this defense registration.',
    ],

    'no_registration_manage_rights' => [
        'title' => 'No rights to manage this registration.',
        'detail' => 'Only lab teacher and submission authors is able to manage this registration.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Review comment errors
    |--------------------------------------------------------------------------
    |
    */

    'review_comment_over_limit' => [
        'title' => 'Review comment is too long.',
        'detail' => 'Review comment\'s length should not exceed 10000 characters.',
    ],

    'review_comment_not_found' => [
        'title'  => 'The review comment could not be found.',
        'detail' => '',
    ],
];
