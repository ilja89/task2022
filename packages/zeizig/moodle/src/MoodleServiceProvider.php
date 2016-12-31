<?php

namespace Zeizig\Moodle;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class MoodleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->publishes([ __DIR__ . '/config' => app()->configPath() ]);
    }

    /**
     * Register Moodle services.
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
            return new \Zeizig\Moodle\Services\PermissionsService($app);
        });
    }

    /**
     * Register blade extensions.
     */
    private function registerBladeExtensions()
    {
        Blade::directive('translate', function ($expression) {
            $translated = translate($expression);
            return "<?php echo \"$translated\"; ?>";
        });
    }

    private function registerHelpers()
    {
        foreach (glob(__DIR__ . '/Helpers/*.php') as $filename) {
            require_once($filename);
        }
    }
}
