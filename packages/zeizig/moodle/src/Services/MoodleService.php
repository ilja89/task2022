<?php

namespace Zeizig\Moodle\Services;

use Illuminate\Contracts\Foundation\Application;

/**
 * Class MoodleService.
 *
 * @package Zeizig\Moodle\Services
 */
abstract class MoodleService
{
    /** @var Application */
    protected $app;

    /**
     * MoodleService constructor.
     *
     * @param  Application  $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
}
