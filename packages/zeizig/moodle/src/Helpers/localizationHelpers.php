<?php

if (!function_exists('translate')) {

    /**
     * Translates the given string.
     *
     * @param  string  $string
     *
     * @return string
     */
    function translate($string) {
        return app()->make(\Zeizig\Moodle\Services\LocalizationService::class)->translate($string);
    }
}
