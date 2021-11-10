<?php

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once __DIR__ . '/plugin/bootstrap/autoload.php';
require_once $CFG->dirroot . '/course/moodleform_mod.php';
require __DIR__ . '/plugin/bootstrap/helpers.php';

class mod_charon_mod_form extends moodleform_mod
{

    function definition()
    {
        $mform = $this->_form;

        require_once __DIR__ . '/plugin/bootstrap/helpers.php';

        /** @var \TTU\Charon\Foundation\Application $app */
        $app = require __DIR__ . '/plugin/bootstrap/app.php';
        /** @var \TTU\Charon\Http\Kernel $kernel */
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

        // Get new request with route since the original one can't be used for routing.
        /** @var \Illuminate\Http\Request $request */
        $request = TTU\Charon\get_moodle_request('instance_form');
        $response = $kernel->handle($request);

        $mform->addElement("html", $response->getContent());

        $currentEditor = null;
        $currentDescription = [
            'text' => '',
            'format' => FORMAT_HTML,
            'itemid' => null,
        ];
        if (isset($this->current->update)) {
            $currentEditor = file_get_submitted_draft_itemid('description');
            $context = \context_module::instance($this->current->update);
            $currenttext = file_prepare_draft_area($currentEditor, $context->id,
                'mod_charon', 'description', 0, [],
                $this->current->description
            );
            $currentDescription = ['text' => $currenttext, 'format' => FORMAT_HTML, 'itemid' => $currentEditor];
        }

        $mform->addElement(
            'editor',
            'description',
            'Description',
            ['rows' => 10],
            [
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'noclean' => true,
                'context' => $this->context,
                'subdirs' => true,
            ]
        )->setValue([
            'text' => $currentDescription['text'],
            'itemid' => $currentEditor,
            'format' => $currentDescription['format'],
        ]);
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', null, 'required', null);

        $this->standard_coursemodule_elements();

        $this->_form->removeElement('cmidnumber');
//        $this->_form->removeElement('groupmode');


        $this->add_action_buttons();

        // This is needed because Laravel catches errors that Moodle produces and ignores while updating module info.
        // If Moodle fixes its code this can be removed.
        set_error_handler(null);
    }

    /**
     * Add elements for setting the custom completion rules.
     *
     * @category completion
     * @return array List of added element names, or names of wrapping group elements.
     */
    public function add_completion_rules() {

        $mform = $this->_form;

        $group = [
            $mform->createElement('text', 'defense_threshold', ' ', 'Threshold (%)'),
        ];
        $mform->setType('defense_threshold', PARAM_INT);
        $mform->addGroup($group, 'completionthresholdgroup', 'Enable activity completion on threshold (0-100%)', [' '], false);

        return ['completionthresholdgroup'];
    }

    /**
     * Called during validation to see whether some module-specific completion rules are selected.
     *
     * @param array $data Input data not yet validated.
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data) {
        return (!empty($data['defense_threshold']) && $data['defense_threshold'] >= 0 && $data['defense_threshold'] <= 100);
    }
}
