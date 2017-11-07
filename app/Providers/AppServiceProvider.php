<?php

namespace App\Providers;

use App\Traits\ActiveBusiness;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    use ActiveBusiness;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \App\GMaps\API::setKey(env('GMAPS_API_KEY'));

        if ($this->app->environment() == 'local') {
            Schema::defaultStringLength(191);
        }

        \View::composer('*', function ($view) {
            $view->with('active_business', $this->business());
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Way\Generators\GeneratorsServiceProvider::class);
            $this->app->register(\Xethron\MigrationsGenerator\MigrationsGeneratorServiceProvider::class);
        }
    }
}
