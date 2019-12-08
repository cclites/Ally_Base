<?php

namespace App\Console\Commands;

use App\Address;
use App\Audit;
use App\Billing\BusinessInvoiceItem;
use App\Billing\CaregiverInvoiceItem;
use App\Billing\ClaimPayment;
use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\ClientPayer;
use App\Billing\Deposit;
use App\Billing\GatewayTransaction;
use App\Billing\Invoiceable\ShiftExpense;
use App\Billing\Payer;
use App\Billing\Payment;
use App\Billing\PaymentLog;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Business;
use App\BusinessChain;
use App\CareDetails;
use App\Caregiver;
use App\CaregiverApplication;
use App\CaregiverLicense;
use App\CaregiverMeta;
use App\CaregiverRestriction;
use App\CarePlan;
use App\Claims\ClaimableExpense;
use App\Claims\ClaimableService;
use App\Claims\ClaimAdjustment;
use App\Claims\ClaimInvoiceItem;
use App\Claims\ClaimRemit;
use App\Client;
use App\ClientContact;
use App\ClientExcludedCaregiver;
use App\ClientGoal;
use App\ClientMedication;
use App\ClientMeta;
use App\ClientNarrative;
use App\ClientOnboarding;
use App\CommunicationLog;
use App\Document;
use App\EmergencyContact;
use App\Note;
use App\OnboardingActivity;
use App\PaymentHold;
use App\PhoneNumber;
use App\Prospect;
use App\Question;
use App\QuickbooksClientInvoice;
use App\QuickbooksConnection;
use App\QuickbooksCustomer;
use App\QuickbooksService;
use App\ReferralSource;
use App\SalesPerson;
use App\ScheduleNote;
use App\Shift;
use App\ShiftConfirmation;
use App\ShiftGoal;
use App\ShiftIssue;
use App\ShiftQuestion;
use App\Signature;
use App\SkilledNursingPoc;
use App\SmsThread;
use App\SmsThreadRecipient;
use App\SmsThreadReply;
use App\SystemNotification;
use App\Task;
use App\TimesheetEntry;
use App\Traits\Console\HasProgressBars;
use App\User;
use Carbon\Carbon;
use Crypt;
use Illuminate\Console\Command;

class ClearSensitiveData extends Command
{
    use HasProgressBars;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:sensitive_data {password : The password to set for all users} {--fast : Mass update values to increase speed} {--reset-key : Reset the application key} {--fix-only : Only fix encrypted values and ignore other data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear sensitive data from a production dump and configure for developer use.';

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var bool
     */
    protected $fastMode = false;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (config('app.env') === 'production') {
            exit('This command cannot be run in production.');
        }

        // Instantiate the faker library for dummy data.
        // Cannot instantiate this in the constructor since
        // production does not have this library.
        $this->faker = \Faker\Factory::create();
        $this->faker->addProvider(new \App\Fakers\Ssn($this->faker));
        $this->faker->addProvider(new \App\Fakers\SimplePhone($this->faker));

        if ($this->option('reset-key')) {
            $this->info('Setting a new application key...');
            $this->call('key:generate');
        }

        if ($this->option('fast')) {
            $this->fastMode = true;
        }

        // Truncate large and otherwise useless tables.
        $this->clearPasswordResets();
        $this->clearAuditLog();
        $this->clearCommunicationsLog();
        $this->cleanClientOnboarding();
        $this->truncateOldSchedulesTable();
        $this->truncateSystemExceptions();
        $this->truncateUsersDeleted();

        // Fix encryption
        $this->cleanEncryptedClientData();
        $this->cleanEncryptedCaregiverData();
        $this->cleanEncryptedClaimsData();
        $this->clean3rdPartyCredentials();
        $this->scrubModel(CaregiverApplication::class);
        $this->scrubModel(ClientMedication::class);
        $this->scrubModel(CreditCard::class);
        $this->scrubModel(BankAccount::class);

        if (! $this->option('fix-only')) {
            // Only execute these options if fix-only if OFF
            $this->scrubModel(Caregiver::class);
            $this->scrubModel(Client::class);
            $this->scrubModel(User::class);
            $this->scrubModel(Address::class);
            $this->scrubModel(PhoneNumber::class);
            $this->scrubModel(EmergencyContact::class);
            $this->scrubModel(ClientContact::class);
            $this->scrubModel(Note::class);
            $this->scrubModel(ScheduleNote::class);
            $this->scrubModel(Shift::class);
            $this->scrubModel(SmsThread::class);
            $this->scrubModel(SmsThreadReply::class);
            $this->scrubModel(SmsThreadRecipient::class);
            $this->scrubModel(ClaimInvoiceItem::class);
            $this->scrubModel(ClaimableService::class);
            $this->scrubModel(ClaimableExpense::class);
            $this->scrubModel(ClaimRemit::class);
            $this->scrubModel(ClaimAdjustment::class);
            $this->scrubModel(ClaimPayment::class);
            $this->scrubModel(BusinessChain::class);
            $this->scrubModel(Business::class);
            $this->scrubModel(BusinessInvoiceItem::class);
            $this->scrubModel(ClientInvoice::class);
            $this->scrubModel(ClientInvoiceItem::class);
            $this->scrubModel(CaregiverInvoiceItem::class);
            $this->scrubModel(CaregiverLicense::class);
            $this->scrubModel(CaregiverMeta::class);
            $this->scrubModel(CaregiverRestriction::class);
            $this->scrubModel(CarePlan::class);
            $this->scrubModel(CareDetails::class);
            $this->scrubModel(ClientExcludedCaregiver::class);
            $this->scrubModel(ClientGoal::class);
            $this->scrubModel(ClientMeta::class);
            $this->scrubModel(ClientNarrative::class);
            $this->scrubModel(ClientPayer::class);
            $this->scrubModel(Deposit::class);
            $this->scrubModel(Payment::class);
            $this->scrubModel(Document::class);
            $this->scrubModel(GatewayTransaction::class);
            $this->scrubModel(Payer::class);
            $this->scrubModel(PaymentHold::class);
            $this->scrubModel(PaymentLog::class);
            $this->scrubModel(Prospect::class);
            $this->scrubModel(Question::class);
            $this->scrubModel(QuickbooksService::class);
            $this->scrubModel(QuickbooksCustomer::class);
            $this->scrubModel(QuickbooksClientInvoice::class);
            $this->scrubModel(QuickbooksConnection::class);
            $this->scrubModel(ReferralSource::class);
            $this->scrubModel(SalesPerson::class);
            $this->scrubModel(ShiftConfirmation::class);
            $this->scrubModel(ShiftExpense::class);
            $this->scrubModel(ShiftQuestion::class);
            $this->scrubModel(ShiftIssue::class);
            $this->scrubModel(ShiftGoal::class);
            $this->scrubModel(Signature::class);
            $this->scrubModel(SkilledNursingPoc::class);
            $this->cleanSystemNotifications();
            $this->scrubModel(Task::class);
            $this->scrubModel(TimesheetEntry::class);
        }

        $this->fixDemoAccounts($this->argument('password'));

        $this->info('Success.');

        return 0;
    }

    /**
     * Clear out the unused users_deleted table.
     */
    public function truncateUsersDeleted() : void
    {
        \DB::table('users_deleted')->truncate();
    }

    /**
     * Clear out old notifications before cleaning the table.
     */
    public function cleanSystemNotifications() : void
    {
        $this->info("Clearing old system notifications...");
        // Delete notifications that are more than 3 months old
        SystemNotification::where('created_at', '<', Carbon::now()->subMonths(1))
            ->delete();

        $this->scrubModel(SystemNotification::class);
    }

    /**
     * Clear the unused system_exceptions table.  This was replaced
     * by 'system notifications'.
     */
    public function truncateSystemExceptions()
    {
        \DB::table('system_exceptions')->truncate();
    }

    /**
     * Clear the unused schedules_old table.
     */
    public function truncateOldSchedulesTable() : void
    {
        \DB::table('schedules_old')->truncate();
    }

    /**
     * Scrub data from the given class using the
     * ScrubsForSeeding trait methods.
     *
     * @param string $class
     */
    public function scrubModel(string $class) : void
    {
        $query = call_user_func("{$class}::getScrubQuery");
        $objectName = str_replace('_', ' ', snake_case(str_plural(class_basename($class))));

        $this->startProgress(
            "Cleaning $objectName...",
            $query->count()
        );

        if ($this->fastMode) {
            $data = call_user_func("{$class}::getScrubbedData", $this->faker, $this->fastMode, null);
            $query->update($data);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) use ($class) {
            \DB::beginTransaction();
            $collection->each(function ($item) use ($class) {
                $data = call_user_func("{$class}::getScrubbedData", $this->faker, $this->fastMode, $item);

                foreach ($data as $key => $val) {
                    // Do not set any values that were previously empty
                    if (empty($item->getOriginal($key))) {
                        unset($data[$key]);
                    }
                }

                if (count($data)) {
                    $item->update($data);
                }

                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function clearCommunicationsLog()
    {
        $this->startProgress('Clearing communications log...',1);

        CommunicationLog::truncate();

        $this->finish();
    }

    public function clearAuditLog()
    {
        $this->startProgress('Clearing audit log...',1);

        Audit::truncate();

        $this->finish();
    }

    public function clearPasswordResets()
    {
        $this->startProgress('Clearing password resets...', 1);
        \DB::table('password_resets')->truncate();
        $this->finish();
    }

    public function cleanClientOnboarding()
    {
        $this->startProgress('Clearing client onboarding data...',1);

        // This feature appears to no longer be in use.
        ClientOnboarding::truncate();
        OnboardingActivity::truncate();

        $this->finish();
    }

    public function clean3rdPartyCredentials()
    {
        $this->startProgress(
            'Clearing 3rd party credentials...',
            Business::count() + 1
        );

        // TODO: implement fast mode?

        // Clear HHA/Tellus credentials
        Business::chunk(200, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (Business $business) {
                if (filled($business->hha_password)) {
                    $business->setHhaPassword('password');
                }

                if (filled($business->tellus_password)) {
                    $business->setTellusPassword('password');
                }

                $business->save();
                $this->advance();
            });
            \DB::commit();
        });

        // Clear Quickbooks access tokens
        QuickbooksConnection::whereRaw(1)->update([
            'access_token' => null,
            'desktop_api_key' => \DB::raw("CONCAT(business_id, '" . substr(md5(Carbon::now()->toDateTimeString()), 0, 29) . "')"),
        ]);
        $this->advance();

        $this->finish();
    }

    public function cleanEncryptedClaimsData()
    {
        $query = ClaimInvoiceItem::whereNotNull('caregiver_ssn');

        $this->startProgress(
            'Cleaning encrypted claims data...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'caregiver_ssn' => Crypt::encrypt($this->faker->ssn),
            ]);

            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (ClaimInvoiceItem $item) {
                $item->update([
                    'caregiver_ssn' => $this->faker->ssn,
                ]);
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanEncryptedCaregiverData()
    {
        $query = Caregiver::withTrashed()->whereNotNull('ssn');

        $this->startProgress(
            'Cleaning encrypted caregiver records...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'ssn' => Crypt::encrypt($this->faker->ssn),
            ]);

            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (Caregiver $caregiver) {
                if ($caregiver->getOriginal('ssn')) {
                    $caregiver->update([
                        'ssn' => $this->faker->ssn,
                    ]);
                }
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanEncryptedClientData()
    {
        $query = Client::withTrashed()->whereNotNull('ssn');

        $this->startProgress(
            'Cleaning encrypted client records...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'ssn' => Crypt::encrypt($this->faker->ssn),
            ]);

            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (Client $client) {
                if ($client->getOriginal('ssn')) {
                    $client->update([
                        'ssn' => $this->faker->ssn,
                    ]);
                }
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function fixDemoAccounts(string $password)
    {
        $this->startProgress(
            'Resetting demo account data...',
            5
        );

        // Change all user passwords
        optional(User::whereRaw(1))->update(['password' => bcrypt($password)]);
        $this->advance();

        // reset demo users account info
        optional(User::find(1))->update(['firstname' => 'Demo', 'lastname' => 'Client', 'username' => 'client@allyms.com']);
        $this->advance();

        optional(User::find(2))->update(['firstname' => 'Demo', 'lastname' => 'User', 'username' => 'officeuser@allyms.com']);
        $this->advance();

        optional(User::find(3))->update(['firstname' => 'Demo', 'lastname' => 'Caregiver', 'username' => 'caregiver@allyms.com']);
        $this->advance();

        optional(User::whereEmail('admin@allyms.com'))->update(['firstname' => 'Admin', 'lastname' => 'User', 'username' => 'admin@allyms.com']);
        $this->advance();

        $this->finish();
    }
}
