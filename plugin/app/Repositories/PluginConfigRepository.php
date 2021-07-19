<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\DB;

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
