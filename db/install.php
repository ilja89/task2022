<?php

/**
 * Post install script. This is run after creating tables from install.xml.
 * Used to populate the database with classification data.
 *
 * @return bool
 */


defined('MOODLE_INTERNAL') || die();

function xmldb_charon_install() {
    global $CFG;
    $charon_path = $CFG->dirroot . "/mod/charon/";
    echo "<pre>";

    if (! in_array($CFG->dbtype, ['mysql', 'mysqli', 'mariadb'])) {
        charon_installation_error("This plugin only supports MySQL/MariaDB databases.");
    }
    if (! function_exists('apache_get_modules') ){
        // There are no Apach modules, if running from cli
        echo "Running from CLI";
        return true;
        // charon_installation_error("This plugin needs apache to redirect requests.");
    }
    if (! in_array('mod_rewrite', apache_get_modules())) {
        charon_installation_error("Please enable mod_rewrite using the following command: sudo a2enmod rewrite");
    }

    echo "</pre>";

    echo "Seeding database\n";

    try {
        require __DIR__ . '/../plugin/bootstrap/autoload.php';
        $app = require __DIR__ . '/../plugin/bootstrap/app.php';
        $kernel = $app->make('Illuminate\Contracts\Console\Kernel');

        $kernel->call('db:seed', ['--class' => 'ClassificationsSeeder']);
        $kernel->call('db:seed', ['--class' => 'PresetsSeeder']);
        $kernel->call('db:seed', ['--class' => 'PlagiarismServicesSeeder']);
        $kernel->call('config:clear');
        $kernel->call('cache:clear');
    } catch (Exception $e) {
        echo "<pre>Exception: ", $e->getMessage(), "</pre>\n";
    }

    return true;
}

if (!function_exists("charon_command_exists")) {
    /**
     * Determines if a command exists on the current environment
     *
     * @param string $command The command to check
     * @return bool True if the command has been found ; otherwise, false.
     */
    function charon_command_exists ($command) {
        $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';

        $process = proc_open(
            "$whereIsCommand $command",
            array(
                0 => array("pipe", "r"), //STDIN
                1 => array("pipe", "w"), //STDOUT
                2 => array("pipe", "w"), //STDERR
            ),
            $pipes
        );
        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);

            return $stdout != '';
        }

        return false;
    }
}
if (!function_exists("charon_is_function_available")) {
    /**
     * Checks if function is disabled or available
     *
     * @param string $command
     * @return bool - true if available
     */
    function charon_is_function_available($command) {
        static $available;

        if (!isset($available)) {
            $available = true;
            if (ini_get('safe_mode')) {
                $available = false;
            } else {
                $d = ini_get('disable_functions');
                $s = ini_get('suhosin.executor.func.blacklist');
                if ("$d$s") {
                    $array = preg_split('/,\s*/', "$d,$s");
                    if (in_array($command, $array)) {
                        $available = false;
                    }
                }
            }
        }

        return $available;
    }
}

if (!function_exists("charon_remove_directory")) {
    function charon_remove_directory($dir) {
        foreach(scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) continue;
            if (is_dir("$dir/$file")) charon_remove_directory("$dir/$file");
            else unlink("$dir/$file");
        }
        rmdir($dir);
    }
}

if (!function_exists("charon_installation_error")) {
    function charon_installation_error($error_msg) {
        global $OUTPUT;

        $progress = new \progress_trace_buffer(new text_progress_trace(), false);
        \core_plugin_manager::instance()->uninstall_plugin("mod_charon", $progress);
        $progress->finished();
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        if (function_exists("purge_all_caches")) {
            purge_all_caches();
        }
        echo "</pre><div class='alert alert-danger alert-block'>". $error_msg ."</div>";
        echo $OUTPUT->continue_button(new moodle_url('/admin/index.php'));
        echo $OUTPUT->footer();
        exit();
    }
}
