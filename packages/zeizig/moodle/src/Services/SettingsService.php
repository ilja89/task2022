<?php

namespace Zeizig\Moodle\Services;

use Zeizig\Moodle\Models\PluginConfig;

/**
 * Class SettingsService.
 *
 * @package Zeizig\Moodle\Services
 */
class SettingsService
{
    /**
     * Get the value for the given setting.
     * The plugin name is in the plugin column in the mdl_config_plugins
     *
     * @param $pluginName
     * @param $settingName
     * @param null $default
     *
     * @return null
     */
    public function getSetting($pluginName, $settingName, $default = null)
    {
        $setting = PluginConfig::where('plugin', $pluginName)
            ->where('name', $settingName)
            ->get();
        if ($setting->isEmpty()) {
            return $default;
        }

        return $setting->first()->value;
    }
}
