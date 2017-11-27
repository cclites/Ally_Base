<?php

namespace App\Providers;

use App\Contracts\ChatServiceInterface;
use App\Gateway\ACHDepositInterface;
use App\Gateway\ACHPaymentInterface;
use App\Gateway\CreditCardPaymentInterface;
use App\Gateway\ECSPayment;
use App\Services\Slack;
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

        $this->app->bind(CreditCardPaymentInterface::class, ECSPayment::class);
        $this->app->bind(ACHDepositInterface::class, ECSPayment::class);
        $this->app->bind(ACHPaymentInterface::class, ECSPayment::class);
        $this->app->bind(ChatServiceInterface::class, function() {
            $slack = new Slack(env('SLACK_ENDPOINT'));
            return $slack->setChannel('#allyms')
                ->setUsername('Ally Bot')
                ->setIconUrl('https://s3.amazonaws.com/teambox-assets/avatars-v2/a052eac951312dc8d2c72c23ac675f8d47540438/thumb.png?1454879401');
        });

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
