<?php

namespace Zeizig\Moodle\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PluginConfig.
 *
 * @property integer $id
 * @property string $plugin
 * @property string $name
 * @property string $value
 *
 * @package Zeizig\Moodle\Models
 */
class PluginConfig extends Model
{
    public $timestamps = false;
    protected $table = 'config_plugins';
}
