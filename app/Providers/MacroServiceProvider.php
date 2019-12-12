<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Collection::macro('bcsum', function ($callback) {
            if (is_null($callback)) {
                return $this->reduce(function ($result, $item) {
                    return add($result, floatval($item));
                }, floatval(0.00));
            }

            $callback = $this->valueRetriever($callback);

            return $this->reduce(function ($result, $item) use ($callback) {
                return add($result, floatval($callback($item)));
            }, floatval(0.00));
        });
    }
}
