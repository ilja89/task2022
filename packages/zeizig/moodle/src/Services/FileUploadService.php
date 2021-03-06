<?php

namespace Zeizig\Moodle\Services;

/**
 * Class FileUploadService.
 * Handle saving of files.
 *
 * @package Zeizig\Moodle\Services
 */
class FileUploadService
{

    /**
     * Saves files from the current request. Needs the field name (eg
     * 'description'), course id (saves files under the course), original intro
     * text (from form).
     *
     * Returns the new text with replaced img urls with @@PLUGIN_NAME@@ or
     * something like that. This should be saved in the database and then while
     * printing out Helpers/rewriteUrlsHelpers rewritePluginIntroUrls() should
     * be called with the same course id and the text returned here.
     *
     * @param  string  $text - The original text from the form
     * @param  string  $formFieldName - Name of the field in mod_form.php. Eg.
     *                                  'description'
     * @param  int  $courseModuleId
     * @param  int  $itemId - Some extra identifier for the file
     *
     * @return string
     */
    public function savePluginFiles(
        $text, $formFieldName, $courseModuleId, $itemId = 0
    ) {
        if (!\App::environment('testing')) {
            $draftid_editor = file_get_submitted_draft_itemid($formFieldName);
            $context = \context_module::instance($courseModuleId);
            $newIntro = file_save_draft_area_files(
                $draftid_editor,
                $context->id,
                'mod_' . config('moodle.plugin_slug'),
                $formFieldName,
                $itemId,
                [],
                $text
            );
        } else {
            $newIntro = $text;
        }

        return $newIntro;
    }
}
