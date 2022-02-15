<?php

/**
 * Class restore_charon_activity_structure_step.
 * Structure step to restore one choice activity.
 */

class restore_charon_activity_structure_step extends restore_activity_structure_step
{
    /**
     * Function that will return the structure to be processed by this restore_step.
     * Must return one array of @restore_path_element elements
     *
     * @throws base_step_exception
     */
    protected function define_structure()
    {
        $paths = [];
        $userInfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('charon', '/activity/charon');
        $paths[] = new restore_path_element('charon_deadline', '/activity/charon/deadlines/deadline');
        $paths[] = new restore_path_element('charon_grademap', '/activity/charon/grademaps/grademap');
        $paths[] = new restore_path_element('charon_template', '/activity/charon/templates/template');

        if ($userInfo) {
            $paths[] = new restore_path_element('charon_teacher_comment', '/activity/charon/teacher_comments/teacher_comment');
            $paths[] = new restore_path_element('charon_submission', '/activity/charon/submissions/submission');
            $paths[] = new restore_path_element('charon_result', '/activity/charon/submissions/submission/results/result');
            $paths[] = new restore_path_element(
                'charon_submission_file', '/activity/charon/submissions/submission/submission_files/submission_file'
            );
            $paths[] = new restore_path_element( 'charon_review_comment',
                '/activity/charon/submissions/submission/submission_files/submission_file/review_comments/review_comment'
            );
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * @param $data
     *
     * @throws base_step_exception
     * @throws dml_exception
     */
    protected function process_charon($data)
    {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();

        $grade_category = new grade_category([
            'courseid' => $data->course,
            'fullname' => $data->name,
        ], false);
        $grade_category->insert();
        $data->category_id = $grade_category->id;

        $newItemId = $DB->insert_record('charon', $data);
        $data->id = $newItemId;

        $this->get_task()->charon = $data;

        $this->apply_activity_instance($newItemId);
    }

    /**
     * @param $data
     * @throws dml_exception
     */
    protected function process_charon_deadline($data)
    {
        global $DB;

        $data = (object) $data;

        $data->charon_id = $this->get_new_parentid('charon');
        if ($data->group_id) {
            $data->group_id = $this->get_mappingid('group', $data->group_id);
        } else {
            $data->group_id = null;
        }

        $DB->insert_record('charon_deadline', $data);
    }

    /**
     * @param $data
     * @throws dml_exception
     */
    protected function process_charon_grademap($data)
    {
        global $DB;

        $data = (object) $data;
        $data->id;

        $data->charon_id = $this->get_new_parentid('charon');
        $data->old_grade_item_id = $data->grade_item_id;
        $data->grade_item_id = null;

        $newId = $DB->insert_record('charon_grademap', $data);

        $data->id = $newId;

        // So that grademaps can later point to grade items
        $this->get_task()->grademaps[] = $data;
    }

    /**
     * @param $data
     * @throws dml_exception
     */
    protected function process_charon_template($data)
    {
        global $DB;

        $data = (object) $data;

        $data->charon_id = $this->get_new_parentid('charon');

        $DB->insert_record('charon_template', $data);
    }

    /**
     * @param $data
     * @throws dml_exception
     */
    protected function process_charon_teacher_comment($data)
    {
        global $DB;

        $data = (object) $data;

        $data->charon_id = $this->get_new_parentid('charon');
        $data->student_id = $this->get_mappingid('user', $data->student_id);
        $data->teacher_id = $this->get_mappingid('user', $data->teacher_id);

        $DB->insert_record('charon_teacher_comment', $data);
    }

    /**
     * @param $data
     * @throws dml_exception
     * @throws restore_step_exception
     */
    protected function process_charon_submission($data)
    {
        global $DB;

        $data = (object) $data;
        $oldId = $data->id;

        $data->charon_id = $this->get_new_parentid('charon');
        $data->user_id = $this->get_mappingid('user', $data->user_id);
        $data->git_callback_id = null;
        $data->original_submission_id = null;
        $data->grader_id = $this->get_mappingid('user', $data->grader_id);

        $newItemId = $DB->insert_record('charon_submission', $data);
        $this->set_mapping('charon_submission', $oldId, $newItemId);
    }

    /**
     * @param $data
     * @throws dml_exception
     */
    protected function process_charon_result($data)
    {
        global $DB;

        $data = (object) $data;

        $data->submission_id = $this->get_new_parentid('charon_submission');
        $data->user_id = $this->get_mappingid('user', $data->user_id);

        $DB->insert_record('charon_result', $data);
    }

    /**
     * @param $data
     * @throws dml_exception
     */
    protected function process_charon_submission_file($data)
    {
        global $DB;

        $data = (object) $data;
        $oldId = $data->id;

        $data->submission_id = $this->get_new_parentid('charon_submission');

        $newItemId = $DB->insert_record('charon_submission_file', $data);
        $this->set_mapping('charon_submission_file', $oldId, $newItemId);

    }

    /**
     * @param $data
     * @throws dml_exception
     */
    protected function process_charon_review_comment($data)
    {
        global $DB;

        $data = (object) $data;

        $data->submission_file_id = $this->get_new_parentid('charon_submission_file');

        $DB->insert_record('charon_review_comment', $data);
    }
}
