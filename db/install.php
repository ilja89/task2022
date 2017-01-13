<?php

require __DIR__ . '/../plugin/bootstrap/autoload.php';

/**
 * Post install script. Run after creating tables from install.xml.
 *
 * @return bool
 */
function xmldb_charon_install() {

    $app = require __DIR__ . '/../plugin/bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');

    $kernel->call('db:seed', ['--class' => 'ClassificationsSeeder']);

    return true;
}
