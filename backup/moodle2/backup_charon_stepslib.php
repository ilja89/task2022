<?php

/**
 * Define all the backup steps that will be used by the backup_charon_activity_task
 */

class backup_charon_activity_structure_step extends \backup_activity_structure_step
{
    /**
     * Define the structure to be processed by this backup step.
     *
     * @return backup_nested_element
     *
     * @throws base_step_exception
     * @throws base_element_struct_exception
     */
    protected function define_structure()
    {
        // To know if we are including user info
        $userinfo = $this->get_setting_value('userinfo');

        // 1. Create a set of backup_nested_element instances that describe the
        //    required data of the plugin

        // Define each element separated

        $charon = new backup_nested_element('charon', ['id'], [
            'category_id', 'name', 'description', 'project_folder', 'tester_extra',
            'system_extra', 'created_at', 'updated_at', 'defense_threshold',
            'tester_type_code', 'grading_method_code', 'intro', 'introformat',
            'timemodified', 'calculation_formula', 'grademax', 'unittests_git',
            'allow_submission'
        ]);


        $deadlines = new backup_nested_element('deadlines');
        $deadline = new backup_nested_element('deadline', ['id'], [
            'deadline_time', 'percentage', 'group_id',
        ]);


        $grademaps = new backup_nested_element('grademaps');
        $grademap = new backup_nested_element('grademap', ['id'], [
            'name', 'grade_type_code', 'grade_item_id'
        ]);


        $teacherComments = new backup_nested_element('teacher_comments');
        $teacherComment = new backup_nested_element('teacher_comment', ['id'], [
            'student_id', 'teacher_id', 'message', 'created_at',
        ]);


        $submissions = new backup_nested_element('submissions');
        $submission = new backup_nested_element('submission', ['id'], [
            'user_id', 'git_hash', 'confirmed', 'git_timestamp', 'git_commit_message',
            'created_at', 'updated_at', 'mail', 'stdout', 'stderr', 'grader_id',
            'original_submission_id',
        ]);


        $results = new backup_nested_element('results');
        $result = new backup_nested_element('result', ['id'], [
            'grade_type_code', 'percentage', 'calculated_result', 'stdout', 'stderr',
        ]);


        $submissionFiles = new backup_nested_element('submission_files');
        $submissionFile = new backup_nested_element('submission_file', ['id'], [
            'path', 'contents',
        ]);

        $reviewComments = new backup_nested_element('review_comments');
        $reviewComment = new backup_nested_element('review_comment', ['id'], [
            'user_id', 'code_row_no_start', 'code_row_no_end', 'review_comment', 'notify', 'created_at'
        ]);

        $templates = new backup_nested_element('templates');
        $template = new backup_nested_element('template', ['id'], [
            'path', 'contents', 'created_at'
        ]);

        // 2. Connect these instances into a hierarchy using their add_child()
        //    method

        // Build the tree

        $charon->add_child($deadlines);
        $deadlines->add_child($deadline);

        $charon->add_child($grademaps);
        $grademaps->add_child($grademap);

        $charon->add_child($teacherComments);
        $teacherComments->add_child($teacherComment);

        $charon->add_child($submissions);
        $submissions->add_child($submission);

        $charon->add_child($templates);
        $templates->add_child($template);

        $submission->add_child($submissionFiles);
        $submissionFiles->add_child($submissionFile);

        $submissionFile->add_child($reviewComments);
        $reviewComments->add_child($reviewComment);

        $submission->add_child($results);
        $results->add_child($result);


        // 3. Set data sources for the elements using their methods like
        //    set_source_table() or set_source_sql()

        // Define sources

        $charon->set_source_sql('
            select 
                c.*,
                gi.calculation as calculation_formula,
                gi.grademax as grademax,
                cs.unittests_git as unittests_git
            from {charon} as c
            inner join {grade_items} as gi
                on c.category_id = gi.iteminstance
            inner join {charon_course_settings} as cs
                on cs.course_id = c.course
            where gi.itemtype = \'category\'
                and c.id = ?
        ', [backup::VAR_ACTIVITYID]);

        $deadline->set_source_table('charon_deadline', ['charon_id' => backup::VAR_PARENTID]);

        $grademap->set_source_table('charon_grademap', ['charon_id' => backup::VAR_PARENTID]);

        $template->set_source_table('charon_template', ['charon_id' => backup::VAR_PARENTID]);

        if ($userinfo) {
            $teacherComment->set_source_table('charon_teacher_comment', ['charon_id' => backup::VAR_PARENTID]);

            $submission->set_source_table('charon_submission', ['charon_id' => backup::VAR_PARENTID]);

            $result->set_source_table('charon_result', ['submission_id' => backup::VAR_PARENTID]);

            $submissionFile->set_source_table('charon_submission_file', ['submission_id' => backup::VAR_PARENTID]);

            $reviewComment->set_source_table('charon_review_comment', ['submission_file_id' => backup::VAR_PARENTID]);
        }


        // Define id annotations

        $charon->annotate_ids('grade_category', 'category_id');

        $deadline->annotate_ids('group', 'group_id');

        $grademap->annotate_ids('grade_item', 'grade_item_id');

        $submission->annotate_ids('user', 'user_id');
        $submission->annotate_ids('user', 'grader_id');

        $teacherComment->annotate_ids('user', 'student_id');
        $teacherComment->annotate_ids('user', 'teacher_id');

        $reviewComment->annotate_ids('user', 'user_id');

        // Define file annotations


        // 4. Return the root backup_nested_element instance processed by the
        //    prepare_activity_structure() method

        // Return the root element (choice), wrapped into standard activity structure


        return $this->prepare_activity_structure($charon);
    }
}
