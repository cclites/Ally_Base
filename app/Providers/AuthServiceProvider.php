<?php

namespace App\Providers;

use App\Activity;
use App\Billing\ClientInvoice;
use App\Billing\Payer;
use App\Billing\Service;
use App\Business;
use App\BusinessChain;
use App\Caregiver;
use App\CaregiverApplication;
use App\Claims\ClaimInvoice;
use App\Claims\ClaimRemit;
use App\Client;
use App\Billing\Deposit;
use App\EmergencyContact;
use App\Billing\GatewayTransaction;
use App\Billing\Payment;
use App\ExpirationType;
use App\PhoneNumber;
use App\Policies\ActivityPolicy;
use App\Policies\BusinessChainPolicy;
use App\Policies\BusinessPolicy;
use App\Policies\CaregiverApplicationPolicy;
use App\Policies\CaregiverPolicy;
use App\Claims\Policies\ClaimInvoicePolicy;
use App\Claims\Policies\ClaimRemitPolicy;
use App\Policies\ClientInvoicePolicy;
use App\Policies\ClientPolicy;
use App\Policies\DepositPolicy;
use App\Policies\EmergencyContactPolicy;
use App\Policies\ExpirationTypePolicy;
use App\Policies\GatewayTransactionPolicy;
use App\Policies\PayerPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PhoneNumberPolicy;
use App\Policies\ProspectPolicy;
use App\Policies\OtherContactPolicy;
use App\Policies\RateCodePolicy;
use App\Policies\ReferralSourcePolicy;
use App\Policies\SchedulePolicy;
use App\Policies\ServicePolicy;
use App\Policies\ShiftPolicy;
use App\Policies\SmsThreadPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TimesheetPolicy;
use App\Policies\UserPolicy;
use App\Policies\CustomFieldPolicy;
use App\Policies\CustomFieldOptionPolicy;
use App\CustomField;
use App\CustomFieldOption;
use App\Prospect;
use App\RateCode;
use App\ReferralSource;
use App\Schedule;
use App\Shift;
use App\SmsThread;
use App\Task;
use App\Timesheet;
use App\User;
use App\OtherContact;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\ClientNarrative;
use App\Policies\ClientNarrativePolicy;
use App\DeactivationReason;
use App\Policies\DeactivationReasonPolicy;
use App\SalesPerson;
use App\Policies\SalesPersonPolicy;
use App\SystemNotification;
use App\Policies\SystemNotificationPolicy;

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
        BusinessChain::class => BusinessChainPolicy::class,
        Caregiver::class => CaregiverPolicy::class,
        CaregiverApplication::class => CaregiverApplicationPolicy::class,
        Client::class => ClientPolicy::class,
        ClientInvoice::class => ClientInvoicePolicy::class,
        ClientNarrative::class => ClientNarrativePolicy::class,
        CustomField::class => CustomFieldPolicy::class,
        CustomFieldOption::class => CustomFieldOptionPolicy::class,
        DeactivationReason::class => DeactivationReasonPolicy::class,
        Deposit::class => DepositPolicy::class,
        EmergencyContact::class => EmergencyContactPolicy::class,
        GatewayTransaction::class => GatewayTransactionPolicy::class,
        OtherContact::class => OtherContactPolicy::class,
        Payer::class => PayerPolicy::class,
        Payment::class => PaymentPolicy::class,
        PhoneNumber::class => PhoneNumberPolicy::class,
        Prospect::class => ProspectPolicy::class,
        RateCode::class => RateCodePolicy::class,
        ReferralSource::class => ReferralSourcePolicy::class,
        SalesPerson::class => SalesPersonPolicy::class,
        Schedule::class => SchedulePolicy::class,
        Service::class => ServicePolicy::class,
        Shift::class => ShiftPolicy::class,
        SmsThread::class => SmsThreadPolicy::class,
        SystemNotification::class => SystemNotificationPolicy::class,
        Task::class => TaskPolicy::class,
        Timesheet::class => TimesheetPolicy::class,
        User::class => UserPolicy::class,
        ExpirationType::class => ExpirationTypePolicy::class,
        ClaimInvoice::class => ClaimInvoicePolicy::class,
        ClaimRemit::class => ClaimRemitPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('view-caregiver-statements', function (User $user, Caregiver $caregiver) {
            return $user->role_type === 'admin'
                || (
                    $user->role_type === 'office_user'
                    && $caregiver->shifts()
                        ->whereNotIn('business_id', $user->getBusinessIds())
                        ->doesntExist()
                );
        });
    }
}
