<?php

/**
 * Post install script. This is run after creating tables from install.xml.
 * Used to populate the database with classification data.
 *
 * @return bool
 */


defined('MOODLE_INTERNAL') || die();

function xmldb_charon_install()
{
    global $CFG;

    $charon_path = $CFG->dirroot . "/mod/charon/";

    if (!in_array($CFG->dbtype, ['mysql', 'mysqli', 'mariadb'])) {
        charon_installation_error("This plugin only supports MySQL/MariaDB databases.");
    }

    try_cleanup($charon_path);

    # shell_exec("php artisan db:seed")

    return true;

}

/**
 * @param string $charon_path
 */
function try_cleanup(string $charon_path)
{
    try {
        echo "\n\nCleaning up...\n";
        $filesToRemove = ["composer-installer.php", "keys.dev.pub", "keys.tags.pub"];
        foreach ($filesToRemove as $filetoRemove) {
            if (file_exists($charon_path . $filetoRemove)) {
                if (unlink($charon_path . $filetoRemove)) {
                    echo "Deleted: " . $filetoRemove . "\n";
                }
            }
        }
        charon_remove_directory("cache");
        echo "Deleted: cache\n";
    } catch (exception $e) {
        echo $e->getMessage();
    }
}

if (!function_exists("charon_remove_directory")) {
    function charon_remove_directory($dir)
    {
        foreach (scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) continue;
            if (is_dir("$dir/$file")) charon_remove_directory("$dir/$file");
            else unlink("$dir/$file");
        }
        rmdir($dir);
    }
}

if (!function_exists("charon_installation_error")) {
    function charon_installation_error($error_msg)
    {
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
        echo "</pre><div class='alert alert-danger alert-block'>" . $error_msg . "</div>";
        echo $OUTPUT->continue_button(new moodle_url('/admin/index.php'));
        echo $OUTPUT->footer();
        exit();
    }
}
