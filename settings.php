<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $settings->add(new admin_setting_configtext(
            'mod_charon/tester_url',
            'Tester URL',
            'The tester url.',
            'neti.ee',
            PARAM_TEXT,
            100)
    );

    $settings->add(new admin_setting_configtext(
            'mod_charon/tester_token',
            'Tester token',
            'Token used to authenticate against tester',
            'ask_from_admin',
            PARAM_TEXT,
            100)
    );

    $settings->add(new admin_setting_configtext(
            'mod_charon/plagiarism_service_url',
            'Plagiarism service URL',
            'The url for the plagiarism service.',
            'neti.ee',
            PARAM_TEXT,
            100)
    );

    $settings->add(new admin_setting_configtext(
            'mod_charon/plagiarism_service_auth_token',
            'Plagiarism service authentication token',
            'The authentication token for the plagiarism service.',
            null,
            PARAM_TEXT,
            100)
    );
}
