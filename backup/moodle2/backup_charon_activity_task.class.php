<?php

require_once($CFG->dirroot . '/mod/charon/backup/moodle2/backup_charon_stepslib.php');

/**
 * Charon backup task that provides all the settings and steps to perform one
 * complete backup of the activity.
 */
class backup_charon_activity_task extends \backup_activity_task
{
    /**
     * Encode URLs that point to the instance.
     *
     * @param string $content
     *
     * @return string
     */
    static public function encode_content_links($content)
    {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to the list of Charons
        $search = '/(' . $base . '\/mod\/charon\/index.php\?id\=)([0-9]+)/';
        $content = preg_replace($search, '$@CHARONINDEX*$2@$', $content);

        // Link to Charon view by moduleid
        $search = '/(' . $base . '\/mod\/charon\/view.php\?id\=)([0-9]+)/';
        $content = preg_replace($search, '$@CHARONVIEWBYID*$2@$', $content);

        return $content;
    }

    /**
     * Define the task as a sequence of steps to take.
     * Usually just $this->add_steps() calls.
     *
     * @throws base_task_exception
     */
    protected function define_my_steps()
    {
        $this->add_step(new backup_charon_activity_structure_step('charon_structure', 'charon.xml'));
    }

    protected function define_my_settings()
    {
        //
    }
}
