<?php

namespace Zeizig\Moodle;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Zeizig\Moodle\Globals\Page;
use Zeizig\Moodle\Globals\User;

/**
 * Class MoodleServiceProvider.
 * Provider for Moodle services.
 *
 * @package Zeizig\Moodle
 */
class MoodleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([ __DIR__ . '/config' => app()->configPath() ]);
    }

    /**
     * Register Moodle services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/moodle.php', 'moodle');

        $this->registerGlobals();

        $this->registerServices();

        $this->registerHelpers();

        $this->registerBladeExtensions();
    }

    /**
     * Register Moodle Globals.
     *
     * @return void
     */
    private function registerGlobals()
    {
        $this->app->singleton(\Zeizig\Moodle\Globals\Output::class, function ($app) {
            return new \Zeizig\Moodle\Globals\Output();
        });
        $this->app->singleton(\Zeizig\Moodle\Globals\Page::class, function ($app) {
            return new \Zeizig\Moodle\Globals\Page();
        });
        $this->app->singleton(\Zeizig\Moodle\Globals\Course::class, function ($app) {
            return new \Zeizig\Moodle\Globals\Course();
        });
        $this->app->singleton(\Zeizig\Moodle\Globals\User::class, function ($app) {
            return new \Zeizig\Moodle\Globals\User();
        });
    }

    /**
     * Register Moodle services.
     *
     * @return void
     */
    private function registerServices()
    {
        $this->app->singleton(\Zeizig\Moodle\Services\GradebookService::class, function ($app) {
            return new \Zeizig\Moodle\Services\GradebookService($app);
        });
        $this->app->singleton(\Zeizig\Moodle\Services\LocalizationService::class, function ($app) {
            return new \Zeizig\Moodle\Services\LocalizationService($app);
        });
        $this->app->singleton(\Zeizig\Moodle\Services\ModuleService::class, function ($app) {
            return new \Zeizig\Moodle\Services\ModuleService($app);
        });
        $this->app->singleton(\Zeizig\Moodle\Services\PermissionsService::class, function ($app) {
            return new \Zeizig\Moodle\Services\PermissionsService($app, new User, new Page);
        });
    }

    /**
     * Register blade extensions.
     *
     * @return void
     */
    private function registerBladeExtensions()
    {
        Blade::directive('translate', function ($expression) {
            $translated = translate($expression);
            return "<?php echo \"$translated\"; ?>";
        });
    }

    /**
     * Register helper files.
     *
     * @return void
     */
    private function registerHelpers()
    {
        foreach (glob(__DIR__ . '/Helpers/*.php') as $filename) {
            require_once($filename);
        }
    }
}
