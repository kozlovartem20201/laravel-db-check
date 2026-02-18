<?php

namespace KozlovArtem\LaravelDbCheck;

use Illuminate\Support\ServiceProvider;
use KozlovArtem\LaravelDbCheck\Commands\CheckDatabaseCommand;

class LaravelDbCheckServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckDatabaseCommand::class,
            ]);
        }
    }
}
