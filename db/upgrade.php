<?php

require __DIR__ . '/../plugin/bootstrap/autoload.php';

function xmldb_charon_upgrade($oldversion = 0)
{
    global $DB;

    if ($oldversion < 2017020102) {
        // We run artisan migrate so we can have all updates as migrations.
        $app = require __DIR__ . '/../plugin/bootstrap/app.php';
        $kernel = $app->make('Illuminate\Contracts\Console\Kernel');

        $kernel->call('migrate', ['--path' => 'plugin/database/migrations']);

        $charons = \TTU\Charon\Models\Charon::all();
        foreach ($charons as $charon) {
            $courseModule = $charon->courseModule();
            if ($courseModule !== null) {
                $charon->course = $courseModule->course;
                $charon->save();
            }
        }
    }

    if ($oldversion < 2017020103) {
        $sql = "ALTER TABLE mdl_charon ADD COLUMN timemodified INTEGER NOT NULL";

        $DB->execute($sql);
    }

    if ($oldversion < 2017021300) {
        $sql = "ALTER TABLE mdl_charon_git_callback ADD COLUMN first_response_time DATETIME";
        $sql2 = "ALTER TABLE mdl_charon_git_callback DROP COLUMN response_received";

        $DB->execute($sql);
        $DB->execute($sql2);
    }

    return true;
}
