<?php

namespace TTU\Charon\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

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
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        if ($this->app->environment() !== 'production') {
            Log::useFiles('php://stderr');
        }
    }
}
