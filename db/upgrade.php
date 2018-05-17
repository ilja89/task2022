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

    if ($oldversion < 2017021301) {
        $sql = "ALTER TABLE mdl_charon_course_settings ADD COLUMN tester_type_code INTEGER";
        $DB->execute($sql);
    }

    if ($oldversion < 2017021500) {
        $sql = "CREATE TABLE mdl_charon_preset(".
               "    id BIGINT AUTO_INCREMENT NOT NULL,".
               "    name VARCHAR(255) NOT NULL,".
               "    parent_category_id BIGINT,".
               "    course_id BIGINT,".
               "    calculation_formula TEXT,".
               "    extra VARCHAR(255),".
               "    PRIMARY KEY (id),".
               "    INDEX IXFK_preset_grade_categories (parent_category_id),".
               "    INDEX IXFK_preset_course (course_id),".
               "    CONSTRAINT FK_preset_grade_categories".
               "        FOREIGN KEY (parent_category_id)".
               "            REFERENCES mdl_grade_categories(id)".
               "            ON DELETE SET NULL".
               "            ON UPDATE CASCADE,".
               "    CONSTRAINT FK_preset_course".
               "        FOREIGN KEY (course_id)".
               "            REFERENCES mdl_course(id)".
               "            ON DELETE SET NULL".
               "            ON UPDATE CASCADE".
               ")";
        $DB->execute($sql);
    }

    if ($oldversion < 2017021501) {
        $sql = "CREATE TABLE mdl_charon_preset_grade(".
                "    id BIGINT AUTO_INCREMENT NOT NULL,".
                "    preset_id BIGINT NOT NULL,".
                "    grade_name_prefix_code BIGINT,".
                "    grade_type_code BIGINT NOT NULL,".
                "    grade_name VARCHAR(255) NOT NULL,".
                "    max_result DECIMAL(10, 2) NOT NULL,".
                "    id_number_postfix VARCHAR(255) NOT NULL,".
                "    PRIMARY KEY (id),".
                "    FOREIGN KEY (preset_id)".
                "        REFERENCES mdl_charon_preset(id)".
                "        ON DELETE CASCADE".
                "        ON UPDATE CASCADE,".
                "    FOREIGN KEY (grade_name_prefix_code)".
                "        REFERENCES mdl_charon_grade_name_prefix(code)".
                "        ON DELETE SET NULL".
                "        ON UPDATE CASCADE,".
                "    FOREIGN KEY (grade_type_code)".
                "        REFERENCES mdl_charon_grade_type(code)".
                "        ON DELETE CASCADE".
                "        ON UPDATE CASCADE,".
                "    INDEX IXFK_preset_grade_preset (preset_id),".
                "    INDEX IXFK_preset_grade_grade_name_prefix (grade_name_prefix_code),".
                "    INDEX IXFK_preset_grade_grade_type (grade_type_code)".
                ")";

        $DB->execute($sql);
    }

    if ($oldversion < 2017021503) {
        $sql = "ALTER TABLE mdl_charon_preset ADD COLUMN grading_method_code BIGINT";
        $sql2 = "ALTER TABLE mdl_charon_preset ADD CONSTRAINT FK_preset_grading_method ".
                "   FOREIGN KEY (grading_method_code)".
                "       REFERENCES mdl_charon_grading_method(code)".
                "       ON DELETE SET NULL".
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

    return true;
}
