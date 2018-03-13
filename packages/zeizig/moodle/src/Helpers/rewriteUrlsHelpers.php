<?php

if (!function_exists('rewriteTextUrls')) {

    /**
     * Rewrites the URLs containing in the given text to actual uploaded file
     * urls.
     *
     * @param  string  $text
     * @param  string  $area
     * @param  int  $courseModuleId
     * @param  int  $itemId - Some extra identifier for the uploaded file, has
     *                        to be the same as when saving the file
     *
     * @return string
     */
    function rewritePluginTextUrls($text, $area, $courseModuleId, $itemId = 0)
    {
        if (\App::environment('testing')) {
            return $text;
        }

        $context = \context_module::instance($courseModuleId);

        return file_rewrite_pluginfile_urls(
            $text,
            'pluginfile.php',  // There might be other ways to save files, this is sort of a configuration
            $context->id,
            'mod_' . config('moodle.plugin_slug'),
            $area,
            $itemId
        );
    }
}
