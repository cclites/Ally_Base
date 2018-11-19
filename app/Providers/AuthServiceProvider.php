<?php

namespace App\Providers;

use App\Activity;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Deposit;
use App\EmergencyContact;
use App\GatewayTransaction;
use App\Payment;
use App\PhoneNumber;
use App\Policies\ActivityPolicy;
use App\Policies\BusinessPolicy;
use App\Policies\CaregiverPolicy;
use App\Policies\ClientPolicy;
use App\Policies\DepositPolicy;
use App\Policies\EmergencyContactPolicy;
use App\Policies\GatewayTransactionPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PhoneNumberPolicy;
use App\Policies\ProspectPolicy;
use App\Policies\RateCodePolicy;
use App\Policies\ReferralSourcePolicy;
use App\Policies\SchedulePolicy;
use App\Policies\ShiftPolicy;
use App\Policies\SystemExceptionPolicy;
use App\Policies\TaskPolicy;
use App\Prospect;
use App\RateCode;
use App\ReferralSource;
use App\Schedule;
use App\Shift;
use App\SystemException;
use App\Task;
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
        Activity::class => ActivityPolicy::class,
        Business::class => BusinessPolicy::class,
        Caregiver::class => CaregiverPolicy::class,
        Client::class => ClientPolicy::class,
        Deposit::class => DepositPolicy::class,
        EmergencyContact::class => EmergencyContactPolicy::class,
        GatewayTransaction::class => GatewayTransactionPolicy::class,
        Payment::class => PaymentPolicy::class,
        PhoneNumber::class => PhoneNumberPolicy::class,
        Prospect::class => ProspectPolicy::class,
        RateCode::class => RateCodePolicy::class,
        ReferralSource::class => ReferralSourcePolicy::class,
        Schedule::class => SchedulePolicy::class,
        Shift::class => ShiftPolicy::class,
        SystemException::class => SystemExceptionPolicy::class,
        Task::class => TaskPolicy::class,
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
