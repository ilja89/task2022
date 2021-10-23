<?php

use TTU\Charon\Repositories\CharonRepository;
use Zeizig\Moodle\Services\GradebookService;

function charon_add_instance($test, $mform)
{
    require_once __DIR__ . '/plugin/bootstrap/helpers.php';
    return TTU\Charon\handle_moodle_request('charons', 'post');
}

function charon_update_instance($test, $mform)
{
    require_once __DIR__ . '/plugin/bootstrap/helpers.php';
    return TTU\Charon\handle_moodle_request('charons/update', 'post');
}

function charon_delete_instance($id)
{
    require_once __DIR__ . '/plugin/bootstrap/helpers.php';

    $app = TTU\Charon\get_app();

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    try {
        // Need to make a request before we can use app->make to get the controller
        // Cannot use routes because this deleting is initiated by the cron job (No server, url parameters etc)
        // TODO: Make this better!
        $kernel->handle($request = \Illuminate\Http\Request::capture());
    } catch (Exception $e) {
    }

    $controller = $app->make(\TTU\Charon\Http\Controllers\InstanceController::class);

    return $controller->destroy($id);
}

function charon_update_grades($modinstance, $userid = 0, $nullifnone = true)
{
}

function charon_grade_item_update($modinstance, $grades = NULL)
{
}

function charon_extend_navigation_course($navigation, $course, $context)
{

    defined('MOODLE_INTERNAL') || die();
    global $DB;

    $sql = "SELECT COUNT(*) as cnt FROM {tag_instance}
             JOIN {tag} ON {tag_instance}.tagid = {tag}.id
             WHERE rawname = 'programming' AND contextid = ?";

    $do_show = $DB->get_record_sql($sql, [$context->id])->cnt != 0;

    if ($do_show && has_capability('moodle/course:manageactivities', $context)) {

        $url = new moodle_url('/mod/charon/courses/' . $course->id . '/settings', []);
        $settingsnode = navigation_node::create('Charon Settings', $url, navigation_node::TYPE_SETTING,
            null, null, new pix_icon('i/settings', ''));
        $navigation->add_node($settingsnode);

        // TODO: Show link only when Charons exist. Capella too!
        $url = new moodle_url('/mod/charon/courses/' . $course->id . '/popup');
        $settingsnode = navigation_node::create('Charon Popup', $url, navigation_node::TYPE_SETTING,
            null, null, new pix_icon('i/settings', ''));
        $navigation->add_node($settingsnode);
    }
}

/**
 * This function accepts a request that requests a file when the
 * requested file is for 'mod_charon'. Also, we don't have to handle files with
 * area 'intro', because that is done automatically by Moodle. If the area is
 * something other than 'intro', this function is called.
 *
 * A file must be sent and the request must be terminated. Or, if no file is
 * found, can return any value and Moodle will automatically send a "File not
 * found" message.
 *
 * There are a bunch of arguments that Moodle sends here, but as far as I can
 * see, they aren't that important, since we have the $context variable.
 *
 * The context should have a level of MODULE, since when saving files, we set
 * the context to be for one course module instance. The file area was set as
 * 'description' on file upload, so we check for that as well.
 *
 * @param  $course - course object
 * @param  $cm - course module object
 * @param  $context - context object
 * @param string $fileArea
 * @param  $args
 * @param bool $forceDownload
 * @param array $options
 *
 * @return bool
 */
function charon_pluginfile(
    $course, $cm, $context, $fileArea, $args, $forceDownload, $options = []
)
{
    if ($context->contextlevel !== CONTEXT_MODULE) {
        // The files are saved under the CONTEXT_MODULE context, so if a request
        // comes in to get a file which isn't attached to a module instance,
        // we can just return false here as in no file was found
        return false;
    }

    require_login();
    if ($fileArea !== 'description') {
        // If file area is not the one we have in the instance form, there will
        // be no files anyways
        return false;
    }

    $itemId = (int)array_shift($args);

    $filename = array_pop($args);
    if (empty($args)) {
        $filePath = '/';
    } else {
        $filePath = '/' . implode('/', $args) . '/';
    }

    $fs = get_file_storage();
    $file = $fs->get_file(
        $context->id, 'mod_charon', $fileArea, $itemId, $filePath, $filename
    );

    if (!$file) {
        return false;
    }

    // Finally send the file, download MUST be forced for security!
    send_stored_file($file, 0, 0, true, $options);
}

function charon_supports($feature)
{

    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        default:
            return null;
    }
}

/**
 * Obtains the completion state for a user that has submitted to a charon
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function charon_get_completion_state($course, $cm, $userid, $type) {
    $gradeService = app(GradebookService::class);
    global $DB;

    // Get charon  details
    $charon = $DB->get_record('charon', array('id' => $cm->instance), '*', MUST_EXIST);

    $threshold = $charon->defense_threshold;

    // If completion option is enabled, evaluate it and return true/false
    if ($threshold && $threshold >= 0 && $threshold <= 100) {

        $categoryGradeItem = $gradeService->getGradeItemByCategoryId($charon->category_id);
        $categoryGradeGrade = $gradeService->getGradeForGradeItemAndUser($categoryGradeItem->id, $userid);

        $category_grade = $categoryGradeItem->grademax;
        $finalgrade = 0;
        if ($categoryGradeGrade !== null) {
            $finalgrade = $categoryGradeGrade->finalgrade;
        }

        return ($threshold * $category_grade / 100) <= $finalgrade;
    } else {
        // Completion option is not enabled so just return $type
        return $type;
    }
}

function update_charon_completion_state($submission, $userId) {
    global $DB, $CFG;
    require_once ($CFG->dirroot . '/lib/completionlib.php');

    $charonRepository = app(CharonRepository::class);
    $charonModel = $charonRepository->getCharonById($submission->charon->id);
    $cm = $charonModel->courseModule();
    $course = $DB->get_record('course', array('id' => $submission->charon->course), '*', MUST_EXIST);

    $completion = new \completion_info($course);

    if ($completion->is_enabled($cm)) {
        $completion->update_state($cm, COMPLETION_COMPLETE, $userId);
    }

    return $submission;
}