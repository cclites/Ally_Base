<?php

namespace App\Providers;

use App\ActiveBusiness;
use App\Businesses\Settings;
use App\Contracts\ChatServiceInterface;
use App\Gateway\ACHDepositInterface;
use App\Gateway\ACHPaymentInterface;
use App\Gateway\CreditCardPaymentInterface;
use App\Gateway\ECSPayment;
use App\Services\Slack;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \App\GMaps\API::setKey(config('services.gmaps.key'));

        $this->app->bind(CreditCardPaymentInterface::class, ECSPayment::class);
        $this->app->bind(ACHDepositInterface::class, ECSPayment::class);
        $this->app->bind(ACHPaymentInterface::class, ECSPayment::class);
        $this->app->bind(ChatServiceInterface::class, function() {
            $slack = new Slack(config('services.slack.endpoint'));
            return $slack->setChannel(config('services.slack.channel'))
                ->setUsername('Ally Bot')
                ->setIconUrl('https://s3.amazonaws.com/teambox-assets/avatars-v2/a052eac951312dc8d2c72c23ac675f8d47540438/thumb.png?1454879401');
        });

        $this->app->singleton('settings', Settings::class);
        $this->app->singleton(ActiveBusiness::class, ActiveBusiness::class);

        if ($this->app->environment() == 'local') {
            Schema::defaultStringLength(191);
        }

        // ALLY-271 Escape curly braces to prevent interpolation, double-encode entities
        \Blade::setEchoFormat('interpol_escape(e(%s, true))');

        \View::composer('*', function ($view) {
            $business = $this->app->make(ActiveBusiness::class);
            $view->with('active_business', $business->get());
        });

        // force rool url if using ngrok
        $appUrl = config('app.url');
        if (str_contains($appUrl, 'ngrok.io')) {
            \URL::forceRootUrl(config('app.url'));
            if (str_contains(config('app.url'), 'https://')) {
                \URL::forceScheme('https');
            }
        }
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
