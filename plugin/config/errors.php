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
];
