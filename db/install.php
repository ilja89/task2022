<?php

require __DIR__ . '/../plugin/bootstrap/autoload.php';

/**
 * Post install script. This is run after creating tables from install.xml.
 * Used to populate the database with classification data.
 *
 * @return bool
 */
function xmldb_charon_install() {

    $app = require __DIR__ . '/../plugin/bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');

    $kernel->call('db:seed', ['--class' => 'ClassificationsSeeder']);
    $kernel->call('cache:clear');

    return true;
}
