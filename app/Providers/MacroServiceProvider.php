<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addBcSumToCollection();
    }

    /**
     * \Illuminate\Support\Collection->bcsum
     *
     * This replicates the behavior of the normal sum() method
     * except it uses our bc math addition helper method to
     * insure there are no floating point issues.
     */
    public function addBcSumToCollection() : void
    {
        \Illuminate\Support\Collection::macro('bcsum', function ($callback) {
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
