<?php

/**
 * Post install script. This is run after creating tables from install.xml.
 * Used to populate the database with classification data.
 *
 * @return bool
 */
function xmldb_charon_install() {

    global $CFG;

    charon_install_composer_dependencies();

    exec('php ' . $CFG->dirroot . '/mod/charon/artisan db:seed --class=ClassificationsSeeder --force');
    exec('php ' . $CFG->dirroot . '/mod/charon/artisan db:seed --class=PresetsSeeder --force');
    exec('php ' . $CFG->dirroot . '/mod/charon/artisan cache:clear');

    return true;
}

if (!function_exists('charon_install_composer_dependencies')) {
    function charon_install_composer_dependencies() {

        global $CFG;
        $charonPath = $CFG->dirroot . '/mod/charon/';

        echo '<pre>';

        charon_install_composer($charonPath);

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
//            '--no-dev' => true
        ));

        $input->setInteractive(false);
        $cmdret = $app->doRun($input,$output);

        echo 'Packages installed.' . "\n";

        echo '</pre>';
    }
}

if (!function_exists('charon_install_composer')) {
    function charon_install_composer($charonPath) {

        if (file_put_contents($charonPath . "composer-installer.php", fopen("https://getcomposer.org/installer", 'r'))) {
            echo "Downloaded composer installer...\n";
        } else {
            echo "Failed to download composer installer.\n";
            return false;
        }

        $composerInstall = "COMPOSER_HOME=\"". $charonPath . "\" php " . $charonPath . "composer-installer.php --install-dir=" . $charonPath;

        echo exec($composerInstall) . "\n";

    }
}
