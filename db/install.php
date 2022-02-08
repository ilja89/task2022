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

    apply_constraints();
    
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

function apply_constraints() {

    global $DB;

    $cmds = array(

        "ALTER TABLE {charon_preset} ADD CONSTRAINT FK_preset_grade_categories " . 
            "FOREIGN KEY (parent_category_id) REFERENCES {grade_categories} (id) ON DELETE SET NULL ON UPDATE CASCADE",

        "ALTER TABLE {charon_preset} ADD CONSTRAINT FK_preset_course " . 
            "FOREIGN KEY (course_id) REFERENCES {course} (id) ON DELETE SET NULL ON UPDATE CASCADE",

        "ALTER TABLE {charon_preset} ADD CONSTRAINT FK_preset_grading_method " . 
            "FOREIGN KEY (grading_method_code) REFERENCES {charon_grading_method} (code) ON DELETE SET NULL ON UPDATE CASCADE",

        "ALTER TABLE {charon_preset_grade} ADD " . 
            "FOREIGN KEY (preset_id) REFERENCES {charon_preset} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_preset_grade} ADD " . 
            "FOREIGN KEY (grade_name_prefix_code) REFERENCES {charon_grade_name_prefix} (code) ON DELETE SET NULL ON UPDATE CASCADE",

        "ALTER TABLE {charon_preset_grade} ADD " . 
            "FOREIGN KEY (grade_type_code) REFERENCES {charon_grade_type} (code) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_lab_teacher} ADD CONSTRAINT FK_charon_lab_teacher_charon_lab " . 
            "FOREIGN KEY (lab_id) REFERENCES {charon_lab} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_lab_teacher} ADD CONSTRAINT FK_charon_lab_teacher_teacher " . 
            "FOREIGN KEY (teacher_id) REFERENCES {user} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_defense_lab} ADD CONSTRAINT FK_charon_defense_lab_charon_lab " . 
            "FOREIGN KEY (lab_id) REFERENCES {charon_lab} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_defense_lab} ADD CONSTRAINT FK_charon_defense_lab_charon " . 
            "FOREIGN KEY (charon_id) REFERENCES {charon} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_defenders} ADD CONSTRAINT FK_charon_defenders_student_id " . 
            "FOREIGN KEY (student_id) REFERENCES {user} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_defenders} ADD CONSTRAINT FK_charon_defenders_charon " . 
            "FOREIGN KEY (charon_id) REFERENCES {charon} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_defenders} ADD CONSTRAINT FK_charon_defenders_submission_id " . 
            "FOREIGN KEY (submission_id) REFERENCES {charon_submission} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_defenders} ADD CONSTRAINT FK_charon_defenders_teacher " . 
            "FOREIGN KEY (teacher_id) REFERENCES {user} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_defenders} ADD CONSTRAINT FK_charon_defenders_charon_defense_lab_id " . 
            "FOREIGN KEY (defense_lab_id) REFERENCES {charon_defense_lab} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_lab} ADD CONSTRAINT FK_charon_lab_course " . 
            "FOREIGN KEY (course_id) REFERENCES {course} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_unit_test} ADD CONSTRAINT FK_charon_unit_test_charon_test_suite " . 
            "FOREIGN KEY (test_suite_id) REFERENCES {charon_test_suite} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon} ADD CONSTRAINT fk_tester_type_code " . 
            " FOREIGN KEY (tester_type_code) REFERENCES {charon_tester_type} (code)",

        "ALTER TABLE {charon_defense_registration} ADD CONSTRAINT FK_defense_registration_student " . 
            " FOREIGN KEY (student_id) REFERENCES {user} (id)",

        "ALTER TABLE {charon_defense_registration} ADD CONSTRAINT FK_defense_registration_charon " . 
            " FOREIGN KEY (charon_id) REFERENCES {charon} (id)",

        "ALTER TABLE {charon_defense_registration} ADD CONSTRAINT FK_defense_registration_submission " . 
            " FOREIGN KEY (submission_id) REFERENCES {charon_submission} (id)",

        "ALTER TABLE {charon_defense_registration} ADD CONSTRAINT FK_defense_registration_teacher " . 
            " FOREIGN KEY (teacher_id) REFERENCES {user} (id)",

        "ALTER TABLE {charon_defense_registration} ADD CONSTRAINT FK_defense_registration_lab " . 
            " FOREIGN KEY (lab_id) REFERENCES {charon_lab} (id)",

        "ALTER TABLE {charon_result} ADD CONSTRAINT FK_result_user " . 
            "FOREIGN KEY (user_id) REFERENCES {user} (id)",

        "ALTER TABLE {charon_lab_group} ADD CONSTRAINT FK_charon_lab_group_charon_lab " . 
            "FOREIGN KEY (lab_id) REFERENCES {charon_lab} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_lab_group} ADD CONSTRAINT FK_charon_lab_group_groups " . 
            "FOREIGN KEY (group_id) REFERENCES {groups} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_template} ADD CONSTRAINT FK_template_charon " . 
            "FOREIGN KEY (charon_id) REFERENCES {charon} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_review_comment} ADD CONSTRAINT FK_charon_review_comment_user " . 
            "FOREIGN KEY (user_id) REFERENCES {user} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_review_comment} ADD CONSTRAINT FK_charon_review_comment_submission_file " . 
            "FOREIGN KEY (submission_file_id) REFERENCES {charon_submission_file} (id) ON DELETE CASCADE ON UPDATE CASCADE",

        "ALTER TABLE {charon_query_logging_enabled} ADD CONSTRAINT FK_query_logging_enabled_user " .
        "FOREIGN KEY (user_id) REFERENCES {user} (id) ON DELETE CASCADE ON UPDATE CASCADE"
    );

    foreach ($cmds as $cmd) {
        $DB->execute($cmd);
    }

}
