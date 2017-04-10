<?php

if (!function_exists('rewriteTextUrls')) {

    /**
     * Rewrites the URLs containing in the given text to actual uploaded file urls.
     *
     * @param  string  $intro
     * @param  int  $courseId
     *
     * @return string
     */
    function rewritePluginIntroUrls($intro, $courseId = null) {

        if (\App::environment('testing')) {
            return $intro;
        }

        if ($courseId === null) {
            $courseId = app(\Zeizig\Moodle\Globals\Course::class)->getCourseId();
        }

        return file_rewrite_pluginfile_urls(
            $intro,
            'pluginfile.php',  // There might be other ways to save files, this is sort of a configuration
            \context_course::instance($courseId)->id,
            'mod_' . config('moodle.plugin_slug'),
            'intro', null
        );
    }
}
