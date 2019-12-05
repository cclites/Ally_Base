<?php

namespace App\Providers;

use App\ActiveBusiness;
use App\Billing\Gateway\DummyGateway;
use App\Businesses\SettingsRepository;
use App\Contracts\ChatServiceInterface;
use App\Billing\Gateway\ACHDepositInterface;
use App\Billing\Gateway\ACHPaymentInterface;
use App\Billing\Gateway\CreditCardPaymentInterface;
use App\Billing\Gateway\ECSPayment;
use App\Contracts\GeocodeInterface;
use App\Services\ConfluenceApiClient;
use App\Services\DummyGeocodeService;
use App\Services\GeocodeManager;
use App\Services\QuickbooksOnlineService;
use App\Services\Slack;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\SFTPReaderReaderWriter;
use App\Services\DummySFTPReaderWriter;
use App\Contracts\SFTPReaderWriterInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * A place to bind abstractions to concretions for the application
     */
    public function bindInterfaces()
    {
        if (config('ally.gateway') == 'ECS') {
            $this->app->bind(CreditCardPaymentInterface::class, ECSPayment::class);
            $this->app->bind(ACHDepositInterface::class, ECSPayment::class);
            $this->app->bind(ACHPaymentInterface::class, ECSPayment::class);
        } else {
            $this->app->bind(CreditCardPaymentInterface::class, DummyGateway::class);
            $this->app->bind(ACHDepositInterface::class, DummyGateway::class);
            $this->app->bind(ACHPaymentInterface::class, DummyGateway::class);
        }

        $this->app->bind(ChatServiceInterface::class, function() {
            $slack = new Slack(config('services.slack.endpoint'));
            return $slack->setChannel(config('services.slack.channel'))
                ->setUsername('Ally Bot')
                ->setIconUrl('https://s3.amazonaws.com/teambox-assets/avatars-v2/a052eac951312dc8d2c72c23ac675f8d47540438/thumb.png?1454879401');
        });

        $this->app->singleton('settings', SettingsRepository::class);
        $this->app->singleton(ActiveBusiness::class, ActiveBusiness::class);

        // SFTP
        if (config('services.sftp.driver') == 'sftp') {
            $this->app->bind(SFTPReaderWriterInterface::class, SFTPReaderReaderWriter::class);
        } else {
            $this->app->bind(SFTPReaderWriterInterface::class, DummySFTPReaderWriter::class);
        }

        $this->app->singleton(QuickbooksOnlineService::class, function() {
            return new QuickbooksOnlineService(
                config('services.quickbooks.client_id'),
                config('services.quickbooks.client_secret'),
                route('business.quickbooks.authorization'),
                config('services.quickbooks.mode')
            );
        });

        $this->app->singleton(ConfluenceApiClient::class, function() {
            return new ConfluenceApiClient(
                config('services.confluence.host', ''),
                config('services.confluence.username', ''),
                config('services.confluence.api_token', '')
            );
        });
    }

    /**
     * A place to map DB polymorphic relationship names to their model classes
     */
    public function mapPolymorphicRelations()
    {
        Relation::morphMap(config('database.polymorphism'));
    }

    /**
     * A place to pass application keys to third party libraries
     */
    public function passApplicationKeys()
    {
        \Packages\GMaps\API::setKey(config('services.gmaps.key'));
    }

    /**
     * A place to add view functions and composers
     */
    public function setupViews()
    {
        // ALLY-271 Escape curly braces to prevent interpolation, double-encode entities
        \Blade::setEchoFormat('interpol_escape(e(%s, true))');

        \View::composer('*', function ($view) {
            $business = $this->app->make(ActiveBusiness::class);
            $view->with('active_business', $business->get());
        });
    }

    /**
     * A place to add logic that is only booted for local environments (developers)
     */
    public function handleLocalEnvironments()
    {
        Schema::defaultStringLength(191);

        // force rool url if using ngrok
        $appUrl = config('app.url');
        if (str_contains($appUrl, 'ngrok.io')) {
            \URL::forceRootUrl(config('app.url'));
            if (str_contains(config('app.url'), 'https://')) {
                \URL::forceScheme('https');
            }
        }

        $this->app->bind(GeocodeInterface::class, DummyGeocodeService::class);
        $this->app->bind(GeocodeManager::class, function() {
             return new GeocodeManager(new DummyGeocodeService());
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bindInterfaces();
        $this->mapPolymorphicRelations();
        $this->passApplicationKeys();
        $this->setupViews();

        if ($this->app->environment() === 'local') {
            $this->handleLocalEnvironments();
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() === 'local') {
            $this->app->register(\Way\Generators\GeneratorsServiceProvider::class);
            $this->app->register(\Xethron\MigrationsGenerator\MigrationsGeneratorServiceProvider::class);
        }
    }
}
