<?php

require __DIR__ . '/../plugin/bootstrap/autoload.php';

/**
 * Exclusively used by the installation and upgrade processes.
 *
 * @link https://docs.moodle.org/dev/Data_definition_API
 * @link https://github.com/moodle/moodle/blob/master/lib/db/upgrade.php
 * @param int $oldversion
 * @return bool
 */
function xmldb_charon_upgrade($oldversion = 0)
{
    global $DB;
    $dbManager = $DB->get_manager();

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

    if ($oldversion < 2017021301) {
        $sql = "ALTER TABLE mdl_charon_course_settings ADD COLUMN tester_type_code INTEGER";
        $DB->execute($sql);
    }

    if ($oldversion < 2017021500) {
        $sql = "CREATE TABLE mdl_charon_preset(" .
            "    id BIGINT AUTO_INCREMENT NOT NULL," .
            "    name VARCHAR(255) NOT NULL," .
            "    parent_category_id BIGINT," .
            "    course_id BIGINT," .
            "    calculation_formula TEXT," .
            "    extra VARCHAR(255)," .
            "    PRIMARY KEY (id)," .
            "    INDEX IXFK_preset_grade_categories (parent_category_id)," .
            "    INDEX IXFK_preset_course (course_id)," .
            "    CONSTRAINT FK_preset_grade_categories" .
            "        FOREIGN KEY (parent_category_id)" .
            "            REFERENCES mdl_grade_categories(id)" .
            "            ON DELETE SET NULL" .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_preset_course" .
            "        FOREIGN KEY (course_id)" .
            "            REFERENCES mdl_course(id)" .
            "            ON DELETE SET NULL" .
            "            ON UPDATE CASCADE" .
            ")";
        $DB->execute($sql);
    }

    if ($oldversion < 2017021501) {
        $sql = "CREATE TABLE mdl_charon_preset_grade(" .
            "    id BIGINT AUTO_INCREMENT NOT NULL," .
            "    preset_id BIGINT NOT NULL," .
            "    grade_name_prefix_code BIGINT," .
            "    grade_type_code BIGINT NOT NULL," .
            "    grade_name VARCHAR(255) NOT NULL," .
            "    max_result DECIMAL(10, 2) NOT NULL," .
            "    id_number_postfix VARCHAR(255) NOT NULL," .
            "    PRIMARY KEY (id)," .
            "    FOREIGN KEY (preset_id)" .
            "        REFERENCES mdl_charon_preset(id)" .
            "        ON DELETE CASCADE" .
            "        ON UPDATE CASCADE," .
            "    FOREIGN KEY (grade_name_prefix_code)" .
            "        REFERENCES mdl_charon_grade_name_prefix(code)" .
            "        ON DELETE SET NULL" .
            "        ON UPDATE CASCADE," .
            "    FOREIGN KEY (grade_type_code)" .
            "        REFERENCES mdl_charon_grade_type(code)" .
            "        ON DELETE CASCADE" .
            "        ON UPDATE CASCADE," .
            "    INDEX IXFK_preset_grade_preset (preset_id)," .
            "    INDEX IXFK_preset_grade_grade_name_prefix (grade_name_prefix_code)," .
            "    INDEX IXFK_preset_grade_grade_type (grade_type_code)" .
            ")";

        $DB->execute($sql);
    }

    if ($oldversion < 2017021503) {
        $sql = "ALTER TABLE mdl_charon_preset ADD COLUMN grading_method_code BIGINT";
        $sql2 = "ALTER TABLE mdl_charon_preset ADD CONSTRAINT FK_preset_grading_method " .
            "   FOREIGN KEY (grading_method_code)" .
            "       REFERENCES mdl_charon_grading_method(code)" .
            "       ON DELETE SET NULL" .
            "       ON UPDATE CASCADE";
        $sql3 = "ALTER TABLE mdl_charon_preset ADD INDEX IXFK_preset_grading_method (grading_method_code)";
        $sql4 = "ALTER TABLE mdl_charon_preset ADD COLUMN max_result DECIMAL(10, 2)";
        $DB->execute($sql);
        $DB->execute($sql2);
        $DB->execute($sql3);
        $DB->execute($sql4);
    }

    if ($oldversion < 2017021600) {
        $app = require __DIR__ . '/../plugin/bootstrap/app.php';
        $kernel = $app->make('Illuminate\Contracts\Console\Kernel');

        $kernel->call('db:seed', ['--class' => 'PresetsSeeder']);
    }

    if ($oldversion < 2017022400) {
        $sql = "ALTER TABLE mdl_charon_preset_grade MODIFY grade_name varchar(255) NULL DEFAULT NULL";
        $sql2 = "ALTER TABLE mdl_charon_preset_grade MODIFY max_result DECIMAL(10, 2) NULL DEFAULT NULL";
        $sql3 = "ALTER TABLE mdl_charon_preset_grade MODIFY id_number_postfix varchar(255) NULL DEFAULT NULL";

        $DB->execute($sql);
        $DB->execute($sql2);
        $DB->execute($sql3);
    }

    if ($oldversion < 2017031300) {
        $sql = "ALTER TABLE mdl_charon_submission ADD COLUMN git_commit_message TEXT";

        $DB->execute($sql);
    }

    if ($oldversion < 2017032700) {
        $sql = "ALTER TABLE mdl_charon_preset_grade DROP FOREIGN KEY mdl_charon_preset_grade_ibfk_3";
        $DB->execute($sql);
    }

    if ($oldversion < 2017100100) {
        $sql = "ALTER TABLE mdl_charon_submission ADD COLUMN git_callback_id INT";
        $DB->execute($sql);
    }

    if ($oldversion < 2017100101) {
        $sql = "ALTER TABLE mdl_charon_submission ADD COLUMN original_submission_id INT";
        $DB->execute($sql);
    }

    if ($oldversion < 2017111102) {
        // Add grader to submission
        $sql = "ALTER TABLE {charon_submission} ADD COLUMN grader_id INT";
        $DB->execute($sql);
    }

    if ($oldversion < 2018051700) {
        $sql = "ALTER TABLE {charon_deadline} ADD COLUMN event_id INT";
        $DB->execute($sql);
    }

    if ($oldversion < 2018063002) {
        # TODO: Make into single change
        $sql = "UPDATE {charon} SET extra = '' WHERE extra IS NULL";
        $DB->execute($sql);
        $sql = "UPDATE {charon_preset} SET extra = '' WHERE extra IS NULL";
        $DB->execute($sql);

        $sql = "ALTER TABLE {charon} CHANGE COLUMN extra tester_extra TEXT";
        $DB->execute($sql);
        $sql = "ALTER TABLE {charon} ADD COLUMN system_extra TEXT";
        $DB->execute($sql);
        $sql = "ALTER TABLE {charon_preset} CHANGE COLUMN extra tester_extra TEXT";
        $DB->execute($sql);
        $sql = "ALTER TABLE {charon_preset} ADD COLUMN system_extra TEXT";
        $DB->execute($sql);
    }

    if ($oldversion < 2018080700) {
        $sql = "CREATE TABLE {charon_plagiarism_service}(" .
            "code INT NOT NULL," .
            "name VARCHAR(255) NOT NULL," .
            "PRIMARY KEY (code)" .
            ")";
        $DB->execute($sql);

        $app = require __DIR__ . '/../plugin/bootstrap/app.php';
        $kernel = $app->make('Illuminate\Contracts\Console\Kernel');

        $kernel->call('db:seed', ['--class' => 'PlagiarismServicesSeeder']);
    }

    if ($oldversion < 2018082000) {
        $sql = "ALTER TABLE {charon} ADD COLUMN plagiarism_checksuite_id VARCHAR(255)";
        $DB->execute($sql);
    }

    if ($oldversion < 2018082100) {
        $sql = "ALTER TABLE {charon} ADD COLUMN plagiarism_latest_check_id VARCHAR(255)";
        $DB->execute($sql);
    }

    if ($oldversion < 2019041000) {
        // is_test field for file
        // {charon_submission_file} ?
        $sql = "alter table mdl_charon_submission_file add column is_test int(1) null";
        $DB->execute($sql);
    }

    if ($oldversion < 2019052801) {
        try {
            $sql = "alter table mdl_charon add column grouping_id int null";
            $DB->execute($sql);
        } catch (dml_write_exception $e) {
            // Ignored intentionally
        }
    }

    if ($oldversion < 2020071801) {
        $sql1 = "CREATE TABLE mdl_charon_lab(" .
            "    id BIGINT(10) AUTO_INCREMENT NOT NULL," .
            "    start DATETIME NOT NULL," .
            "    end DATETIME NOT NULL," .
            "    PRIMARY KEY (id)" .
            ")";
        $DB->execute($sql1);
        $sql2 = "CREATE TABLE mdl_charon_lab_teacher(" .
            "    id BIGINT(10) AUTO_INCREMENT NOT NULL," .
            "    lab_id BIGINT(10) NOT NULL," .
            "    teacher_id BIGINT(10) NOT NULL," .
            "    PRIMARY KEY (id)," .
            "    INDEX IXFK_charon_lab_teacher_charon_lab (lab_id)," .
            "    INDEX IXFK_charon_lab_teacher_teacher (teacher_id)," .
            "    CONSTRAINT FK_charon_lab_teacher_charon_lab" .
            "        FOREIGN KEY (lab_id)" .
            "            REFERENCES mdl_charon_lab(id)" .
            "            ON DELETE CASCADE," .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_charon_lab_teacher_teacher" .
            "        FOREIGN KEY (teacher_id)" .
            "            REFERENCES mdl_user(id)" .
            "            ON DELETE CASCADE," .
            "            ON UPDATE CASCADE" .
            ")";
        $DB->execute($sql2);
        $sql3 = "CREATE TABLE mdl_charon_defense_lab(" .
            "    id BIGINT(10) AUTO_INCREMENT NOT NULL," .
            "    lab_id BIGINT(10) NOT NULL," .
            "    charon_id BIGINT(10) NOT NULL," .
            "    PRIMARY KEY (id)," .
            "    INDEX IXFK_charon_defense_lab_charon_lab (lab_id)," .
            "    INDEX IXFK_charon_defense_lab_charon (charon_id)," .
            "    CONSTRAINT FK_charon_defense_lab_charon_lab" .
            "        FOREIGN KEY (lab_id)" .
            "            REFERENCES mdl_charon_lab(id)" .
            "            ON DELETE CASCADE," .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_charon_defense_lab_charon" .
            "        FOREIGN KEY (charon_id)" .
            "            REFERENCES mdl_charon(id)" .
            "            ON DELETE CASCADE," .
            "            ON UPDATE CASCADE" .
            ")";
        $DB->execute($sql3);
        $sql4 = "CREATE TABLE mdl_charon_defenders(" .
            "    id BIGINT(10) AUTO_INCREMENT NOT NULL," .
            "    student_id BIGINT(10) NOT NULL," .
            "    charon_id BIGINT(10) NOT NULL," .
            "    student_name VARCHAR(254) NOT NULL," .
            "    submission_id BIGINT(10) NOT NULL," .
            "    choosen_time DATETIME NOT NULL," .
            "    my_teacher TINYINT(1) NOT NULL," .
            "    teacher_id BIGINT(10) NOT NULL," .
            "    defense_lab_id BIGINT(10) NOT NULL," .
            "    PRIMARY KEY (id)," .
            "    INDEX IXFK_charon_defenders_student_id (student_id)," .
            "    INDEX IXFK_charon_defenders_charon (charon_id)" .
            "    INDEX IXFK_charon_defenders_submission_id (submission_id)," .
            "    INDEX IXFK_charon_defenders_teacher (teacher_id)" .
            "    INDEX IXFK_charon_defenders_charon_defense_lab_id (defense_lab_id)," .
            "    CONSTRAINT FK_charon_defenders_student_id" .
            "        FOREIGN KEY (student_id)" .
            "            REFERENCES mdl_user(id)" .
            "            ON DELETE CASCADE," .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_charon_defenders_charon" .
            "        FOREIGN KEY (charon_id)" .
            "            REFERENCES mdl_charon(id)" .
            "            ON DELETE CASCADE," .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_charon_defenders_submission_id" .
            "        FOREIGN KEY (submission_id)" .
            "            REFERENCES mdl_charon_submission(id)" .
            "            ON DELETE CASCADE," .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_charon_defenders_teacher" .
            "        FOREIGN KEY (teacher_id)" .
            "            REFERENCES mdl_user(id)" .
            "            ON DELETE CASCADE," .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_charon_defenders_charon_defense_lab_id" .
            "        FOREIGN KEY (defense_lab_id)" .
            "            REFERENCES mdl_charon_defense_lab(id)" .
            "            ON DELETE CASCADE," .
            "            ON UPDATE CASCADE" .
            ")";

        $DB->execute($sql4);

    }

    if ($oldversion < 2020071803) {
        $sql1 = "ALTER TABLE mdl_charon ADD COLUMN defense_deadline DATETIME NOT NULL";
        $sql2 = "ALTER TABLE mdl_charon ADD COLUMN defense_duration INT NOT NULL";
        $sql3 = "ALTER TABLE mdl_charon ADD COLUMN choose_teacher BOOL NOT NULL";
        $DB->execute($sql1);
        $DB->execute($sql2);
        $DB->execute($sql3);
    }

    if ($oldversion < 2020071901) {
        $sql1 = "ALTER TABLE mdl_charon MODIFY defense_deadline DATETIME NULL DEFAULT NULL";
        $sql2 = "ALTER TABLE mdl_charon MODIFY defense_duration INT NULL DEFAULT NULL";
        $sql3 = "ALTER TABLE mdl_charon MODIFY choose_teacher BOOL NULL DEFAULT NULL";
        $DB->execute($sql1);
        $DB->execute($sql2);
        $DB->execute($sql3);
    }

    if ($oldversion < 2020071902) {
        $sql1 = "ALTER TABLE mdl_charon MODIFY defense_deadline DATETIME NULL";
        $sql2 = "ALTER TABLE mdl_charon MODIFY defense_duration INT NULL";
        $sql3 = "ALTER TABLE mdl_charon MODIFY choose_teacher BOOL NOT NULL";
        $DB->execute($sql1);
        $DB->execute($sql2);
        $DB->execute($sql3);
    }

    if ($oldversion < 2020071903) {
        $sql = "ALTER TABLE mdl_charon MODIFY choose_teacher BOOL NULL";
        $DB->execute($sql);
    }

    if ($oldversion < 2020072001) {
        $sql = "ALTER TABLE mdl_charon_lab ADD COLUMN course_id BIGINT(10) NOT NULL";
        $sql2 = "ALTER TABLE mdl_charon_lab ADD CONSTRAINT FK_charon_lab_course" .
            "   FOREIGN KEY (course_id)" .
            "       REFERENCES mdl_course(id)" .
            "       ON DELETE CASCADE" .
            "       ON UPDATE CASCADE";
        $sql3 = "ALTER TABLE mdl_charon_lab ADD INDEX IXFK_charon_lab_charon (course_id)";
        $DB->execute($sql);
        $DB->execute($sql2);
        $DB->execute($sql3);
    }

    if ($oldversion < 2020080701) {
        $sql = "ALTER TABLE mdl_charon_defenders ADD COLUMN progress VARCHAR(255) NOT NULL DEFAULT 'Waiting'";
        $DB->execute($sql);
    }

    if ($oldversion < 2020081401) {

        $sql = "CREATE TABLE mdl_charon_test_suite(" .
            "    id BIGINT(10) AUTO_INCREMENT NOT NULL," .
            "    name VARCHAR(255) NOT NULL," .
            "    file VARCHAR(255) NOT NULL," .
            "    start_date DATETIME ," .
            "    end_date DATETIME ," .
            "    weight NOT NULL INT DEFAULT 1," .
            "    passed_count INT NOT NULL," .
            "    grade FLOAT NOT NULL," .
            "    PRIMARY KEY (id)" .
            ")";

        $sql2 = "CREATE TABLE mdl_charon_unit_test(" .
            "    id BIGINT(10) AUTO_INCREMENT NOT NULL," .
            "    test_suite_id BIGINT(10) NOT NULL," .
            "    groups_depended_upon VARCHAR(255)," .
            "    status VARCHAR(255) NOT NULL," .
            "    weight INT NOT NULL DEFAULT 1," .
            "    print_exception_message TINYINT(1)," .
            "    print_stack_trace TINYINT(1)," .
            "    time_elapsed INT," .
            "    methods_depended_upon VARCHAR(255)," .
            "    stack_trace VARCHAR(255)," .
            "    name VARCHAR(255) NOT NULL," .
            "    stdout VARCHAR(255)," .
            "    exception_class VARCHAR(255)," .
            "    exception_message VARCHAR(255)," .
            "    stderr VARCHAR(255)," .
            "    PRIMARY KEY (id)," .
            "    INDEX IXFK_charon_unit_test_charon_test_suite (test_suite_id)," .
            "    CONSTRAINT FK_charon_unit_test_charon_test_suite" .
            "        FOREIGN KEY (test_suite_id)" .
            "            REFERENCES mdl_charon_test_suite(id)" .
            "            ON DELETE CASCADE" .
            "            ON UPDATE CASCADE" .
            ")";

        $DB->execute($sql);
        $DB->execute($sql2);
    }

    if ($oldversion < 2020081601) {
        $sql = "ALTER TABLE mdl_charon_test_suite ADD COLUMN submission_id BIGINT(10) NOT NULL";
        $DB->execute($sql);
    }

    if ($oldversion < 2020082201) {
        $sql = "ALTER TABLE mdl_charon_unit_test CHANGE COLUMN groups_dependedUpon groups_depended_upon VARCHAR(255)";
        $sql2 = "DROP TABLE mdl_charon_submission_test_suite";
        $DB->execute($sql);
        $DB->execute($sql2);
    }

    if ($oldversion < 2020082701) {
        try {
            $sql = "ALTER TABLE mdl_charon ADD COLUMN defense_threshold BIGINT(10) DEFAULT 50";
            $DB->execute($sql);
        } catch (dml_write_exception $e) {
            // Ignored intentionally
        }
    }

    // FROM THIS POINT ON PLEASE USE $CFG->prefix instead of `mdl_` as prefix !!!
    global $CFG;

    if ($oldversion < 2020091201) {
        try {
            $sql = "ALTER TABLE " . $CFG->prefix . "charon_lab_teacher ADD COLUMN teacher_location VARCHAR(255)";
            $DB->execute($sql);
            $sql = "ALTER TABLE " . $CFG->prefix . "charon_lab_teacher ADD COLUMN teacher_comment VARCHAR(255)";
            $DB->execute($sql);
        } catch (dml_write_exception $e) {
            // Ignored intentionally
        }
    }

    if ($oldversion < 2020100601) {
        try {
            $sql2 = "ALTER TABLE " . $CFG->prefix . "charon_defenders ADD UNIQUE (choosen_time, student_id)";
            $DB->execute($sql2);
            $sql = "ALTER TABLE " . $CFG->prefix . "charon_defenders ADD UNIQUE (choosen_time, teacher_id)";
            $DB->execute($sql);
        } catch (dml_write_exception $e) {
            // Ignored intentionally
        }
    }

    if ($oldversion < 2020100603) {

        $sql2 = "DROP TABLE IF EXISTS " . $CFG->prefix . "charon_defenders";
        $DB->execute($sql2);

        $sql4 = "CREATE TABLE " . $CFG->prefix . "charon_defenders(" .
            "    id BIGINT(10) AUTO_INCREMENT NOT NULL," .
            "    student_id BIGINT(10) NOT NULL," .
            "    charon_id BIGINT(10) NOT NULL," .
            "    student_name VARCHAR(254) NOT NULL," .
            "    submission_id BIGINT(10) NOT NULL," .
            "    choosen_time DATETIME NOT NULL," .
            "    my_teacher TINYINT(1) NOT NULL," .
            "    teacher_id BIGINT(10) NOT NULL," .
            "    defense_lab_id BIGINT(10) NOT NULL," .
            "    teacher_location VARCHAR(255) NULL," .
            "    teacher_comment VARCHAR(255) NULL," .
            "    progress VARCHAR(255) NOT NULL DEFAULT 'Waiting'," .
            "    PRIMARY KEY (id)," .
            "    CONSTRAINT IXUNIQUE_choosen_time_and_student_id UNIQUE (student_id, choosen_time)," .
            "    CONSTRAINT IXUNIQUE_choosen_time_and_teacher_id UNIQUE (teacher_id, choosen_time)," .
            "    INDEX IXFK_charon_defenders_student_id (student_id)," .
            "    INDEX IXFK_charon_defenders_charon (charon_id)," .
            "    INDEX IXFK_charon_defenders_submission_id (submission_id)," .
            "    INDEX IXFK_charon_defenders_teacher (teacher_id)," .
            "    INDEX IXFK_charon_defenders_charon_defense_lab_id (defense_lab_id)," .
            "    CONSTRAINT FK_charon_defenders_student_id" .
            "        FOREIGN KEY (student_id)" .
            "            REFERENCES " . $CFG->prefix . "user(id)" .
            "            ON DELETE CASCADE" .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_charon_defenders_charon" .
            "        FOREIGN KEY (charon_id)" .
            "            REFERENCES " . $CFG->prefix . "charon(id)" .
            "            ON DELETE CASCADE" .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_charon_defenders_submission_id" .
            "        FOREIGN KEY (submission_id)" .
            "            REFERENCES " . $CFG->prefix . "charon_submission(id)" .
            "            ON DELETE CASCADE" .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_charon_defenders_teacher" .
            "        FOREIGN KEY (teacher_id)" .
            "            REFERENCES " . $CFG->prefix . "user(id)" .
            "            ON DELETE CASCADE" .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_charon_defenders_charon_defense_lab_id" .
            "        FOREIGN KEY (defense_lab_id)" .
            "            REFERENCES " . $CFG->prefix . "charon_defense_lab(id)" .
            "            ON DELETE CASCADE" .
            "            ON UPDATE CASCADE" .
            ")";

        $DB->execute($sql4);
    }

    if ($oldversion < 2020102301) {
        $sql1 = "ALTER TABLE " . $CFG->prefix . "charon ADD COLUMN defense_start_time DATETIME";
        $DB->execute($sql1);
    }

    if ($oldversion < 2020102905) {
        $table = new xmldb_table("charon");

        $fields = [
            new xmldb_field("docker_timeout", XMLDB_TYPE_INTEGER, "6"),
            new xmldb_field("docker_content_root", XMLDB_TYPE_TEXT),
            new xmldb_field("docker_test_root", XMLDB_TYPE_TEXT),
            new xmldb_field("group_size", XMLDB_TYPE_INTEGER, "4")
        ];

        foreach ($fields as $field) {
            if (!$dbManager->field_exists($table, $field)) {
                $dbManager->add_field($table, $field);
            }
        }

        try {
            // There is no key_exists, so test the equivalent index.
            $oldIndex = new xmldb_index('fk_tester_type_code', XMLDB_KEY_FOREIGN, ['tester_type_code'], 'charon_tester_type', ['code']);
            if (!$dbManager->index_exists($table, $oldIndex)) {
                $DB->execute("SET FOREIGN_KEY_CHECKS=0");
                $DB->execute(
                    "ALTER TABLE {charon} ADD CONSTRAINT fk_tester_type_code FOREIGN KEY (tester_type_code) " .
                    "REFERENCES {charon_tester_type} (code)"
                );
            }
        } finally {
            $DB->execute("SET FOREIGN_KEY_CHECKS=1");
        }
    }

    if ($oldversion < 2020122001) {
        $table = new xmldb_table("charon_submission_user");

        $table->add_field('submission_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'user_id');

        $table->add_key('FK_submission_to_user_submission_id', XMLDB_KEY_FOREIGN, ['submission_id'], 'charon_submission', ['id']);
        $table->add_key('FK_submission_to_user_user_id', XMLDB_KEY_FOREIGN, ['user_id'], 'user', ['id']);

        $table->add_index('IXUNIQUE_submission_and_user', XMLDB_INDEX_UNIQUE, ['submission_id', 'user_id']);

        if (!$dbManager->table_exists($table)) {
            $dbManager->create_table($table);
        }
    }

    if ($oldversion < 2021011801) {
        $DB->execute(
            "INSERT IGNORE INTO " . $CFG->prefix . "charon_submission_user (submission_id, user_id)"
                . " SELECT submission.id, submission.user_id"
                . " FROM " . $CFG->prefix . "charon_submission AS submission"
        );
    }

    if ($oldversion < 2021020601) {
        $table = new xmldb_table('charon_grademap');
        $field = new xmldb_field('persistent', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0, null);

        if (!$dbManager->field_exists($table, $field)) {
            $dbManager->add_field($table, $field);
        }
    }

    if ($oldversion < 2021021001) {
        $table = new xmldb_table('charon_lab');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null, null);

        if (!$dbManager->field_exists($table, $field)) {
            $dbManager->add_field($table, $field);
        }
    }

    if ($oldversion < 2021021601) {
        $sql = "CREATE TABLE " . $CFG->prefix . "charon_defense_registration(" .
            "    id BIGINT(10) AUTO_INCREMENT NOT NULL," .
            "    student_id BIGINT(10) NULL," .
            "    charon_id BIGINT(10) NULL," .
            "    submission_id BIGINT(10) NULL," .
            "    teacher_id BIGINT(10) NOT NULL," .
            "    lab_id BIGINT(10) NOT NULL," .
            "    time DATETIME NOT NULL," .
            "    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
            "    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
            "    progress VARCHAR(24) NOT NULL DEFAULT 'New'," .
            "    PRIMARY KEY (id)," .
            "    CONSTRAINT UQ_defense_registration_teacher_and_time UNIQUE (teacher_id, time)," .
            "    CONSTRAINT FK_defense_registration_student" .
            "        FOREIGN KEY (student_id)" .
            "            REFERENCES " . $CFG->prefix . "user(id)," .
            "    CONSTRAINT FK_defense_registration_charon" .
            "        FOREIGN KEY (charon_id)" .
            "            REFERENCES " . $CFG->prefix . "charon(id)," .
            "    CONSTRAINT FK_defense_registration_submission" .
            "        FOREIGN KEY (submission_id)" .
            "            REFERENCES " . $CFG->prefix . "charon_submission(id)," .
            "    CONSTRAINT FK_defense_registration_teacher" .
            "        FOREIGN KEY (teacher_id)" .
            "            REFERENCES " . $CFG->prefix . "user(id)," .
            "    CONSTRAINT FK_defense_registration_lab" .
            "        FOREIGN KEY (lab_id)" .
            "            REFERENCES " . $CFG->prefix . "charon_lab(id))";

        $table = new xmldb_table("charon_defense_registration");

        if (!$dbManager->table_exists($table)) {
            $DB->execute($sql);
        }
    }

    if ($oldversion < 2021022403) {
        // Add new user_id field
        $table = new xmldb_table('charon_result');
        $field = new xmldb_field('user_id', XMLDB_TYPE_INTEGER, '10', null, null, null, 0, 'submission_id');

        if (!$dbManager->field_exists($table, $field)) {
            $dbManager->add_field($table, $field);
        }

        // Update existing data to match new structure
        $records = $DB->get_records_select('charon_result', "user_id = 0");
        if (count($records) > 0) {
            $transaction = $DB->start_delegated_transaction();

            try {
                // Add submission main authors as users in results table
                $DB->execute(
                    "UPDATE {charon_result} AS cr "
                        . "INNER JOIN {charon_submission} AS cs ON cr.submission_id = cs.id "
                        . "SET cr.user_id = cs.user_id"
                );
                // Add additional rows for every co-author
                $DB->execute(
                    "INSERT INTO {charon_result}(submission_id, user_id, grade_type_code, percentage, calculated_result, stdout, stderr) "
                        . "SELECT "
                        . "cmu.submission_id, "
                        . "cmu.user_id, "
                        . "cr.grade_type_code, "
                        . "cr.percentage, "
                        . "cr.calculated_result, "
                        . "cr.stdout, "
                        . "cr.stderr "
                        . "FROM {charon_submission_user} AS cmu "
                        . "INNER JOIN {charon_submission} AS cs ON cmu.submission_id = cs.id "
                        . "RIGHT JOIN {charon_result} AS cr ON cmu.submission_id = cr.submission_id "
                        . "WHERE cmu.user_id != cs.user_id"
                );

                $transaction->allow_commit();
            } catch(Exception $exception) {
                $transaction->rollback($exception);
                throw $exception;
            }
        }

        // Set user_id not null
        $field = new xmldb_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $dbManager->change_field_notnull($table, $field);

        // Add constraints and indexes
        $DB->execute("ALTER TABLE {charon_result} ADD INDEX IXFK_result_user (user_id)");
        $DB->execute("ALTER TABLE {charon_result} ADD INDEX IXFK_result_user_submission (user_id,submission_id)");
        $DB->execute(
            "ALTER TABLE {charon_result} ADD CONSTRAINT UK_charon_result_submission_user_grade_type_code "
                . "UNIQUE (submission_id,user_id,grade_type_code)"
        );
        $DB->execute(
            "ALTER TABLE {charon_result} ADD CONSTRAINT FK_result_user FOREIGN KEY (user_id) "
                . "REFERENCES {user} (id)"
        );
    }

    if ($oldversion < 2021052601) {
        $sql = "CREATE TABLE " . $CFG->prefix . "charon_lab_group(" .
            "    id BIGINT(10) AUTO_INCREMENT NOT NULL," .
            "    lab_id BIGINT(10) NOT NULL," .
            "    group_id BIGINT(10) NOT NULL," .
            "    PRIMARY KEY (id)," .
            "    INDEX IXFK_charon_lab_group_charon_lab (lab_id)," .
            "    CONSTRAINT UQ_charon_lab_lab_and_group UNIQUE (lab_id, group_id)," .
            "    CONSTRAINT FK_charon_lab_group_charon_lab" .
            "        FOREIGN KEY (lab_id)" .
            "            REFERENCES " . $CFG->prefix . "charon_lab(id)" .
            "            ON DELETE CASCADE" .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_charon_lab_group_groups" .
            "        FOREIGN KEY (group_id)" .
            "            REFERENCES " . $CFG->prefix . "groups(id)" .
            "            ON DELETE CASCADE" .
            "            ON UPDATE CASCADE" .
            ")";

        $table = new xmldb_table("charon_lab_group");

        if (!$dbManager->table_exists($table)) {
            $DB->execute($sql);
        }
    }

    if ($oldversion < 2021062801) {
        $table = new xmldb_table("charon_course_settings");
        $field = new xmldb_field('tester_url', XMLDB_TYPE_CHAR, 255, null, null, null, null, null, null);

        if (!$dbManager->field_exists($table, $field)) {
            $dbManager->add_field($table, $field);
        }

        $field = new xmldb_field('tester_token', XMLDB_TYPE_CHAR, 255, null, null, null, null, null, null);

        if (!$dbManager->field_exists($table, $field)) {
            $dbManager->add_field($table, $field);
        }
    }

    if ($oldversion < 2021071302){
        $sql = "CREATE TABLE " . $CFG->prefix . "charon_template(" .
            "    id BIGINT(10) AUTO_INCREMENT NOT NULL," .
            "    charon_id BIGINT(10) NOT NULL," .
            "    path TEXT NOT NULL," .
            "    contents TEXT," .
            "    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
            "    PRIMARY KEY (id)," .
            "    INDEX IXFK_template_charon (charon_id)," .
            "    CONSTRAINT FK_template_charon" .
            "        FOREIGN KEY (charon_id)" .
            "            REFERENCES " . $CFG->prefix . "charon(id)" .
            ")";

        $table = new xmldb_table("charon_template");

        if (!$dbManager->table_exists($table)) {
            $DB->execute($sql);
        }
    }

    if ($oldversion < 2021081101){
        $table = new xmldb_table("charon");
        $field = new xmldb_field("allow_submission", XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, 0);

        if (!$dbManager->field_exists($table, $field)) {
            $dbManager->add_field($table, $field);
        }
    }

    if ($oldversion < 2021090901){
        $table = new xmldb_table("charon_course_settings");
        $field = new xmldb_field('tester_sync_url', XMLDB_TYPE_CHAR, 255, null, null, null, null, null, null);

        if (!$dbManager->field_exists($table, $field)) {
            $dbManager->add_field($table, $field);
        }
    }

    if ($oldversion < 2021101101){
        $sql = "ALTER TABLE " . $CFG->prefix . "charon_template DROP CONSTRAINT IF EXISTS FK_template_charon";
        $DB->execute($sql);
        $sql = "ALTER TABLE " . $CFG->prefix . "charon_template ADD CONSTRAINT FK_template_charon FOREIGN KEY (charon_id) "
            . "REFERENCES " . $CFG->prefix . "charon (id) ON DELETE CASCADE ON UPDATE CASCADE";
        $DB->execute($sql);
    }

    if ($oldversion < 2021101101) {
        $sql = "CREATE TABLE " . $CFG->prefix . "charon_review_comment(" .
            "    id BIGINT(10) AUTO_INCREMENT NOT NULL," .
            "    user_id BIGINT(10) NOT NULL," .
            "    submission_file_id BIGINT(10) NOT NULL," .
            "    code_row_no_start BIGINT(10) NULL," .
            "    code_row_no_end BIGINT(10) NULL," .
            "    review_comment TEXT NOT NULL," .
            "    notify BOOL NOT NULL DEFAULT FALSE," .
            "    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
            "    PRIMARY KEY (id)," .
            "    INDEX IXFK_charon_review_comment_user (user_id)," .
            "    INDEX IXFK_charon_review_comment_submission_file (submission_file_id)," .
            "    CONSTRAINT FK_charon_review_comment_user" .
            "        FOREIGN KEY (user_id)" .
            "            REFERENCES " . $CFG->prefix . "user(id)" .
            "            ON DELETE CASCADE" .
            "            ON UPDATE CASCADE," .
            "    CONSTRAINT FK_charon_review_comment_submission_file" .
            "        FOREIGN KEY (submission_file_id)" .
            "            REFERENCES " . $CFG->prefix . "charon_submission_file(id)" .
            "            ON DELETE CASCADE" .
            "            ON UPDATE CASCADE" .
            ")";

        $table = new xmldb_table("charon_review_comment");

        if (!$dbManager->table_exists($table)) {
            $DB->execute($sql);
        }
    }

    if ($oldversion < 2021113001) {
        $tableName = "charon_grading_method";
        $record = ["code" => 3, "name" => "prefer_best_each_test_grade"];
        if (!$DB->record_exists($tableName, $record)) {
            $DB->insert_record($tableName, $record, false); // returns inserted id by default
        }
    }

    return true;
}
