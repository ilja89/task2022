<?php

namespace TTU\Charon\Facades;

use Illuminate\Support\Facades\Facade;

class MoodleConfig extends Facade
{

    public $dbtype    = '';
    public $dblibrary = '';
    public $dbhost    = '';
    public $dbname    = '';
    public $dbuser    = '';
    public $dbpass    = '';
    public $prefix    = '';
    public $dboptions = [];
    public $wwwroot   = '';
    public $dataroot  = '';
    public $dirroot   = '';
    public $admin     = '';
    public $directorypermissions = 0777;

    /**
     * MoodleConfig constructor.
     */
    public function __construct()
    {
        if (file_exists(__DIR__ . '/../../../../../config.php')) {
            require __DIR__ . '/../../../../../config.php';
            global $CFG;

            foreach($CFG as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @Override
     */
    protected static function getFacadeAccessor()
    {
        return 'moodle';
    }
}
