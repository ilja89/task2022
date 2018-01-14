<?php

require_once($CFG->dirroot . '/mod/charon/backup/moodle2/restore_charon_stepslib.php');

/**
 * Charon restore task that provides all the settings and steps to perform one
 * complete restore of the activity.
 */
class restore_charon_activity_task extends restore_activity_task
{
    public $grademaps = [];
    public $charon = null;

    /**
     * Define (add) particular steps that each activity can have.
     *
     * @throws base_task_exception
     */
    protected function define_my_steps()
    {
        $this->add_step(new restore_charon_activity_structure_step('charon_structure', 'charon.xml'));
    }

    /**
     * Define (add) particular settings that each activity can have.
     */
    protected function define_my_settings()
    {
        //
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder.
     */
    static public function define_decode_contents()
    {
        $contents = [];

        $contents[] = new restore_decode_content('charon', ['intro', 'description'], 'charon');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder.
     */
    static public function define_decode_rules()
    {
        $rules = [];

        $rules[] = new restore_decode_rule('CHARONVIEWBYID', '/mod/charon/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('CHARONINDEX', '/mod/charon/index.php?id=$1', 'course');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * choice logs. It must return one array
     * of {@link restore_log_rule} objects.
     */
    static public function define_restore_log_rules()
    {
        $rules = [];

        $rules[] = new restore_log_rule('charon', 'add', 'view.php?id={course_module}', '{charon}');
        $rules[] = new restore_log_rule('charon', 'update', 'view.php?id={course_module}', '{charon}');
        $rules[] = new restore_log_rule('charon', 'view', 'view.php?id={course_module}', '{charon}');
        $rules[] = new restore_log_rule('charon', 'choose', 'view.php?id={course_module}', '{charon}');
        $rules[] = new restore_log_rule('charon', 'choose again', 'view.php?id={course_module}', '{charon}');
        $rules[] = new restore_log_rule('charon', 'report', 'report.php?id={course_module}', '{charon}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects.
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0).
     */
    static public function define_restore_log_rules_for_course()
    {
        $rules = [];

        // Fix old wrong uses (missing extension)
        $rules[] = new restore_log_rule('charon', 'view all', 'index?id={course}', null,
            null, null, 'index.php?id={course}');
        $rules[] = new restore_log_rule('charon', 'view all', 'index.php?id={course}', null);

        return $rules;
    }

    /**
     * @throws dml_exception
     */
    public function after_restore()
    {
        $this->updateGradeItemsAndGrademaps();

        $this->updateCategoryInfo();
    }

    /**
     * Move grade items under the Charon grade category and update the grademaps
     * references to the grade items.
     *
     * @throws dml_exception
     */
    private function updateGradeItemsAndGrademaps()
    {
        global $DB;

        $courseId = $this->get_courseid();
        $charonId = $this->charon->id;
        $sql = '
            update {charon_grademap} as gm
            inner join {grade_items} as gi
            on gi.iteminstance = gm.charon_id
            and gi.itemnumber = gm.grade_type_code
            
            set 
                gm.grade_item_id = gi.id,
                gi.categoryid = ?
                
            where gi.courseid = ?
            and gi.itemtype = \'mod\'
            and gi.itemmodule = \'charon\'
            and gm.charon_id = ?
        ';

        $DB->execute($sql, [$this->charon->category_id, $courseId, $charonId]);
    }

    /**
     * Update the calculation formula and max grade for the new Charon category.
     *
     * @throws dml_exception
     */
    private function updateCategoryInfo()
    {
        global $DB;

        $grademaps = $DB->get_records('charon_grademap', [
            'charon_id' => $this->charon->id,
        ]);

        $calculationFormula = $this->charon->calculation_formula;
        $gradeItemIds = [];
        foreach ($grademaps as $savedGrademap) {
            foreach ($this->grademaps as $oldGrademap) {
                if ((int) $oldGrademap->id === (int) $savedGrademap->id) {
                    $gradeItemIds[$oldGrademap->old_grade_item_id] = (int) $savedGrademap->grade_item_id;
                }
            }
        }

        foreach ($gradeItemIds as $oldId => $newId) {
            $calculationFormula = preg_replace(
                "/##gi{$oldId}##/", "##gi{$newId}##", $calculationFormula
            );
        }

        $sql = '
            update {grade_items} as gi
            set 
                gi.calculation = ?,
                gi.grademax = ?
            where gi.itemtype = \'category\'
            and gi.iteminstance = ?
        ';

        $DB->execute($sql, [
            $calculationFormula,
            $this->charon->grademax,
            $this->charon->category_id,
        ]);
    }
}
