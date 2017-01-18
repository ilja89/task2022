<?php

$observers = [
    [
        'eventname' => '\core\event\course_module_created',
        'callback'  => '\mod_charon\course_module_created::course_module_created',
        'includefile' => 'mod/charon/classes/event/course_module_created.php',
        'internal'  => false,
    ],
    [
        'eventname' => '\core\event\course_module_updated',
        'callback'  => '\mod_charon\course_module_updated::course_module_updated',
        'includefile' => 'mod/charon/classes/event/course_module_updated.php',
        'internal'  => false,
    ],
];
