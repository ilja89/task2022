<?php

namespace Zeizig\Moodle\Services;

/**
 * Class LocalizationService.
 * This is a Moodle wrapper for localization and translating strings.
 *
 * More about Moodle "Strings": https://docs.moodle.org/dev/String_API
 *
 * @package Zeizig\Moodle\Services
 */
class LocalizationService extends MoodleService
{
    /**
     * Translate a given string. Takes translations from lang/ folder in plugin root.
     *
     * @param  string $string
     * @param  string  $module
     *
     * @return string
     */
    public function translate($string, $module = null)
    {
        if ($module === null) {
            return get_string($string, config('moodle.plugin_slug'));
        } else {
            return get_string($string, $module);
        }
    }
}
