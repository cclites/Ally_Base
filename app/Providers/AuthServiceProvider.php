<?php

namespace App\Providers;

use App\EmergencyContact;
use App\PhoneNumber;
use App\Policies\EmergencyContactPolicy;
use App\Policies\PhoneNumberPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        PhoneNumber::class => PhoneNumberPolicy::class,
        EmergencyContact::class => EmergencyContactPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
