<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Class PluginRepository.
 * Used to get charon plugin information.
 *
 * @package TTU\Charon\Repositories
 */
class PluginConfigRepository
{
    public function getMoodleVersion()
    {
        return DB::table('config_plugins')
            ->where('plugin', 'mod_charon')
            ->where('name', 'version')
            ->select('value')
            ->get();
    }
}
