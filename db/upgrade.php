<?php

require __DIR__ . '/../plugin/bootstrap/autoload.php';

function xmldb_charon_upgrade($oldversion = 0)
{
    if ($oldversion < 2016112800) {
        // We run artisan migrate so we can have all updates as migrations.
        $app = require __DIR__ . '/../plugin/bootstrap/app.php';
        $kernel = $app->make('Illuminate\Contracts\Console\Kernel');

        $kernel->call('migrate', ['--path' => 'plugin/database/migrations']);
    }

    return true;
}
