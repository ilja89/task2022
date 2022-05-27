<?php


defined('MOODLE_INTERNAL') || die();

function xmldb_charon_uninstall() {

    global $DB;
    $DB->execute("SET FOREIGN_KEY_CHECKS=0");

    return true;
}

