<?php

require __DIR__ . '/../plugin/bootstrap/autoload.php';

/**
 * Post install script. This is run after creating tables from install.xml.
 * Used to populate the database with classification data.
 *
 * @return bool
 */
function xmldb_charon_install() {

    charon_install_composer_dependencies();

    $app = require __DIR__ . '/../plugin/bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');

    $kernel->call('db:seed', ['--class' => 'ClassificationsSeeder']);
    $kernel->call('db:seed', ['--class' => 'PresetsSeeder']);
    $kernel->call('cache:clear');

    return true;
}

if (!function_exists('charon_install_composer_dependencies')) {
    function charon_install_composer_dependencies() {

        global $CFG;
        $charonPath = $CFG->dirroot . '/mod/charon';

        require_once "phar://" . $charonPath . "composer.phar/src/bootstrap.php";
        chdir($charonPath);
        putenv("COMPOSER_HOME={$charonPath}");
        putenv("COMPOSER={$charonPath}composer.json");
        putenv("COMPOSER_VENDOR_DIR={$charonPath}vendor");
        putenv("OSTYPE=OS400");
        $app = new \Composer\Console\Application();
        $factory = new \Composer\Factory();
        $output = $factory->createOutput();
        $input = new \Symfony\Component\Console\Input\ArrayInput(array(
            'command' => 'install',
            '--no-progress' => true,
            '--no-dev' => true
        ));
        $input->setInteractive(false);
        $cmdret = $app->doRun($input,$output);
    }
}
