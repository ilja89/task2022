<?php

namespace Zeizig\Moodle\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Zeizig\Moodle\Models\Module;

/**
 * Class ModuleService.
 * Used to get and cache the plugin module ID.
 *
 * @package TTU\Charon\Services
 */
class ModuleService extends MoodleService
{
    /**
     * Gets the module ID from the cache or database. This is the ID in mdl_module table.
     * Also caches the value.
     *
     * @return integer
     */
    public function getModuleId()
    {
        return Cache::remember('plugin_module_id', Carbon::now()->addDay(), function () {
            return Module::where('name', config('moodle.plugin_slug'))->first()->id;
        });
    }
}
