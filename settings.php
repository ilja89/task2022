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
            50)
    );
}
