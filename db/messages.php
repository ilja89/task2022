<?php

defined('MOODLE_INTERNAL') || die();
$messageproviders = array (
    // Comment on a student's charon submission
    'comment' => array (
        'defaults' => array (
            'popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN + MESSAGE_DEFAULT_LOGGEDOFF,
            'email' => MESSAGE_PERMITTED,
        ),
    )
);

