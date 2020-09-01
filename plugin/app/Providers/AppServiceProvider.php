<?php

namespace TTU\Charon\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

use TTU\Charon\Foundation\Application;

class AppServiceProvider extends ServiceProvider
{
    /** @var Application */
    protected $app;

    /**
     * AppServiceProvider constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setToStringFormat('Y-m-d H:i:s');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            Log::useFiles('php://stderr');
        } else {
            global $CFG;
            Log::useFiles($CFG->dataroot . "/charon_logs.log");
            Log::useFiles('php://stderr');
        }
    }
}
