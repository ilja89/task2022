<?php

if (!function_exists('translate')) {

    /**
     * Translates the given string.
     *
     * @param  string  $string
     * @param  string  $module
     *
     * @return string
     */
    function translate($string, $module = null) {
        return app()->make(\Zeizig\Moodle\Services\LocalizationService::class)->translate($string, $module);
    }
}
