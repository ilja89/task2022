<?php

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once __DIR__ . '/plugin/bootstrap/autoload.php';
require_once $CFG->dirroot . '/course/moodleform_mod.php';
require __DIR__ . '/plugin/bootstrap/helpers.php';

class mod_charon_mod_form extends moodleform_mod
{

    function definition()
    {
        /** @var \TTU\Charon\Foundation\Application $app */
        $app = require __DIR__ . '/plugin/bootstrap/app.php';
        /** @var \TTU\Charon\Http\Kernel $kernel */
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

        // Get new request with route since the original one can't be used for routing.
        /** @var \Illuminate\Http\Request $request */
        $request = getMoodleRequest('instance_form');
        $response = $kernel->handle($request = $request);

        $this->_form->addElement("html", $response->getContent());

        $this->_form->addElement(
            'editor',
            'description',
            'Description',
            ['rows' => 10], [
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'noclean'  => true,
                'context'  => $this->context,
                'subdirs'  => true
            ]
        )->setValue(['text' => isset($this->current->description) ? $this->current->description : '']);
        $this->_form->setType('description', PARAM_RAW);
        $this->_form->addRule('description', null, 'required', null);

        $this->standard_coursemodule_elements();
        $this->_form->removeElement('cmidnumber');
        $this->_form->removeElement('groupmode');

        $this->add_action_buttons();

        // This is needed because Laravel catches errors that Moodle produces and ignores while updating module info.
        // If Moodle fixes its code this can be removed.
        set_error_handler(null);
    }
}
