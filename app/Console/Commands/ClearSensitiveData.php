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
use App\Billing\Payer;
use App\Billing\Payment;
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
use App\Claims\ClaimInvoice;
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
use App\PhoneNumber;
use App\QuickbooksConnection;
use App\ScheduleNote;
use App\Shift;
use App\SmsThread;
use App\SmsThreadRecipient;
use App\SmsThreadReply;
use App\Traits\Console\HasProgressBars;
use App\User;
use Crypt;
use Illuminate\Console\Command;
use OwenIt\Auditing\Auditable;

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

    // SoftDeletes models for reference:
    // X CarePlan
    // X Document
    // X ClaimAdjustment
    // X ClaimRemit
    // X Client
    // X Caregiver
    // Admin
    // Question
    // QuickbooksService
    // Schedule
    // Task
    // FailedTransaction
    // OfficeUser

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

        $this->scrubModel(Business::class);
        exit;

        // Truncate large and otherwise useless tables.
        $this->clearPasswordResets();
        $this->clearAuditLog();
        $this->clearCommunicationsLog();
        $this->cleanClientOnboarding();

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
            $this->scrubModel(Payment::class);

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
            $this->scrubModel(ClaimInvoice::class);
            $this->scrubModel(ClaimableService::class);
            $this->scrubModel(ClaimableExpense::class);
            $this->scrubModel(ClaimRemit::class);
            $this->scrubModel(ClaimAdjustment::class);
            $this->scrubModel(ClaimPayment::class);
            $this->scrubModel(BusinessChain::class);
            $this->scrubModel(Business::class);
            $this->cleanBusinessInvoiceItems();
            $this->cleanClientInvoices();
            $this->cleanClientInvoiceItems();
            $this->cleanCaregiverInvoiceItems();
            $this->cleanCaregiverLicenses();
            $this->cleanCaregiverMeta();
            $this->cleanCaregiverRestrictions();
            $this->cleanCarePlans();
            $this->cleanClientCareDetails();
            $this->cleanExcludedCaregivers();
            $this->cleanClientGoals();
            $this->cleanClientMeta();
            $this->cleanClientNarrative();
            $this->cleanClientPayers();
            $this->cleanDeposits();
            $this->cleanPayments();
            $this->cleanDocuments();
            $this->cleanGatewayTransaction();
            $this->cleanPayers();
        }

        $this->fixDemoAccounts($this->argument('password'));

        $this->info('Success.');

        return 0;
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
            $data = call_user_func("{$class}::getScrubbedData", $this->faker, $this->fastMode);
            $query->update($data);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) use ($class) {
            \DB::beginTransaction();
            $collection->each(function ($item) use ($class) {
                $data = call_user_func("{$class}::getScrubbedData", $this->faker, $this->fastMode);

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
        QuickbooksConnection::whereRaw(1)->update(['access_token' => null]);
        $this->advance();

        $this->finish();
    }
























    public function cleanPayers()
    {
        $query = Payer::query();

        $this->startProgress(
            'Cleaning Payers...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'email' => $this->faker->email,
                'address1' => $this->faker->streetAddress,
                'npi_number' => $this->faker->randomNumber(9),
                'phone_number' => $this->generatePhoneNumber(),
                'fax_number' => $this->generatePhoneNumber(),
                'contact_name' => $this->faker->name,
            ]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (Payer $item) {
                if (filled($item->getOriginal('email'))) {
                    $item->email = $this->faker->email;
                }
                if (filled($item->getOriginal('address1'))) {
                    $item->address1 = $this->faker->streetAddress;
                }
                if (filled($item->getOriginal('npi_number'))) {
                    $item->npi_number = $this->faker->randomNumber(9);
                }
                if (filled($item->getOriginal('phone_number'))) {
                    $item->phone_number = $this->generatePhoneNumber();
                }
                if (filled($item->getOriginal('fax_number'))) {
                    $item->fax_number = $this->generatePhoneNumber();
                }
                if (filled($item->getOriginal('contact_name'))) {
                    $item->contact_name = $this->faker->name;
                }
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanGatewayTransaction()
    {
        $query = GatewayTransaction::query();

        $this->startProgress(
            'Cleaning transaction records...',
            $query->count()
        );

        // always fast mode
        $query->update([
            'routing_number' => $this->faker->randomNumber(4),
            'account_number' => $this->faker->randomNumber(4),
        ]);
        $this->finish();
    }

    public function cleanDeposits()
    {
        $query = Deposit::query();

        $this->startProgress(
            'Cleaning Deposit...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'notes' => $this->faker->sentence,
            ]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (Deposit $item) {
                if (filled($item->getOriginal('notes'))) {
                    $item->notes = $this->faker->sentence;
                }
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanPayments()
    {
        $query = Payment::query();

        $this->startProgress(
            'Cleaning Payments...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'notes' => $this->faker->sentence,
            ]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (Payment $item) {
                if (filled($item->getOriginal('notes'))) {
                    $item->notes = $this->faker->sentence;
                }
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanDocuments()
    {
        $query = Document::withTrashed();

        $this->startProgress(
            'Cleaning documents...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'original_filename' => $this->faker->word.'.pdf',
                'description' => $this->faker->word,
            ]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (Document $item) {
                $item->original_filename = $this->faker->word.'.pdf';
                $item->description = $this->faker->word;
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClientPayers()
    {
        $query = ClientPayer::whereNotNull('notes')->orWhereNotNull('policy_number');

        $this->startProgress(
            'Cleaning client payers...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'policy_number' => $this->faker->randomNumber(9),
                'notes' => $this->faker->sentence,
            ]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (ClientPayer $item) {
                if (filled($item->getOriginal('notes'))) {
                    $item->notes = $this->faker->sentence;
                }
                if (filled($item->getOriginal('policy_number'))) {
                    $item->policy_number = $this->faker->randomNumber(9);
                }
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClientNarrative()
    {
        $query = ClientNarrative::query();

        $this->startProgress(
            'Cleaning ClientNarrative...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'notes' => $this->faker->sentence,
            ]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (ClientNarrative $item) {
                if (filled($item->getOriginal('notes'))) {
                    $item->notes = $this->faker->sentence;
                }
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClientCareDetails()
    {
        $query = CareDetails::query();

        $this->startProgress(
            'Cleaning client care details...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'medication_overseer' => $this->faker->sentence,
                'allergies' => $this->faker->sentence,
                'pharmacy_name' => $this->faker->sentence,
                'pharmacy_number' => $this->faker->sentence,
                'safety_instructions' => $this->faker->sentence,
                'toileting_instructions' => $this->faker->sentence,
                'bathing_frequency' => $this->faker->sentence,
                'bathing_instructions' => $this->faker->sentence,
                'hearing_instructions' => $this->faker->sentence,
                'feeding_instructions' => $this->faker->sentence,
                'diet_likes' => $this->faker->sentence,
                'skin_conditions' => $this->faker->sentence,
                'hair_frequency' => $this->faker->sentence,
                'shaving_instructions' => $this->faker->sentence,
                'dressing_instructions' => $this->faker->sentence,
                'housekeeping_instructions' => $this->faker->sentence,
                'supplies_instructions' => $this->faker->sentence,
                'comments' => $this->faker->sentence,
                'instructions' => $this->faker->sentence,
            ]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (CareDetails $item) {
                if (filled($item->getOriginal('medication_overseer'))) {
                    $item->medication_overseer = $this->faker->sentence;
                }
                if (filled($item->getOriginal('allergies'))) {
                    $item->allergies = $this->faker->sentence;
                }
                if (filled($item->getOriginal('pharmacy_name'))) {
                    $item->pharmacy_name = $this->faker->sentence;
                }
                if (filled($item->getOriginal('pharmacy_number'))) {
                    $item->pharmacy_number = $this->faker->sentence;
                }
                if (filled($item->getOriginal('safety_instructions'))) {
                    $item->safety_instructions = $this->faker->sentence;
                }
                if (filled($item->getOriginal('toileting_instructions'))) {
                    $item->toileting_instructions = $this->faker->sentence;
                }
                if (filled($item->getOriginal('bathing_frequency'))) {
                    $item->bathing_frequency = $this->faker->sentence;
                }
                if (filled($item->getOriginal('bathing_instructions'))) {
                    $item->bathing_instructions = $this->faker->sentence;
                }
                if (filled($item->getOriginal('hearing_instructions'))) {
                    $item->hearing_instructions = $this->faker->sentence;
                }
                if (filled($item->getOriginal('feeding_instructions'))) {
                    $item->feeding_instructions = $this->faker->sentence;
                }
                if (filled($item->getOriginal('diet_likes'))) {
                    $item->diet_likes = $this->faker->sentence;
                }
                if (filled($item->getOriginal('skin_conditions'))) {
                    $item->skin_conditions = $this->faker->sentence;
                }
                if (filled($item->getOriginal('hair_frequency'))) {
                    $item->hair_frequency = $this->faker->sentence;
                }
                if (filled($item->getOriginal('shaving_instructions'))) {
                    $item->shaving_instructions = $this->faker->sentence;
                }
                if (filled($item->getOriginal('dressing_instructions'))) {
                    $item->dressing_instructions = $this->faker->sentence;
                }
                if (filled($item->getOriginal('housekeeping_instructions'))) {
                    $item->housekeeping_instructions = $this->faker->sentence;
                }
                if (filled($item->getOriginal('supplies_instructions'))) {
                    $item->supplies_instructions = $this->faker->sentence;
                }
                if (filled($item->getOriginal('comments'))) {
                    $item->comments = $this->faker->sentence;
                }
                if (filled($item->getOriginal('instructions'))) {
                    $item->instructions = $this->faker->sentence;
                }
                if ($item->getOriginal('notes')) {
                    $item->notes = $this->faker->sentence;
                }
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClientMeta()
    {
        $query = ClientMeta::query();

        $this->startProgress(
            'Cleaning client custom fields...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update(['value' => $this->faker->word]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (ClientMeta $item) {
                $item->value = $this->faker->word;
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanCarePlans()
    {
        $query = CarePlan::withTrashed();

        $this->startProgress(
            'Cleaning care plans...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'name' => $this->faker->name,
                'notes' => $this->faker->sentence,
            ]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (CarePlan $item) {
                $item->name = $this->faker->name;
                if ($item->getOriginal('notes')) {
                    $item->notes = $this->faker->sentence;
                }
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClientGoals()
    {
        $query = ClientGoal::withTrashed();

        $this->startProgress(
            'Cleaning client goals...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'question' => $this->faker->sentence.'?',
            ]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function ($item) {
                $item->question = $this->faker->sentence.'?';
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanExcludedCaregivers()
    {
        $query = ClientExcludedCaregiver::whereNotNull('note');

        $this->startProgress(
            'Cleaning client excluded caregivers...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'note' => $this->faker->sentence,
            ]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function ($item) {
                if (filled($item->getOriginal('note'))) {
                    $item->note = $this->faker->sentence;
                }
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanCaregiverRestrictions()
    {
        $query = CaregiverRestriction::query();

        $this->startProgress(
            'Cleaning caregiver restrictions...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update(['description' => $this->faker->sentence]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (CaregiverRestriction $item) {
                $item->description = $this->faker->sentence;
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanCaregiverMeta()
    {
        $query = CaregiverMeta::query();

        $this->startProgress(
            'Cleaning caregiver custom fields...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update(['value' => $this->faker->word]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (CaregiverMeta $item) {
                $item->value = $this->faker->word;
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanCaregiverLicenses()
    {
        $query = CaregiverLicense::query();

        $this->startProgress(
            'Cleaning caregiver licenses...',
            $query->count()
        );

        if ($this->fastMode) {
            $name = strtoupper($this->faker->randomLetter() . $this->faker->randomLetter() . $this->faker->randomLetter());
            $query->update([
                'name' => $name,
                'description' => "$name Certification",
            ]);

            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (CaregiverLicense $item) {
                $item->name = strtoupper($this->faker->randomLetter() . $this->faker->randomLetter() . $this->faker->randomLetter());
                $item->description = $item->name . ' Certification';
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanBusinessInvoiceItems()
    {
        $query = BusinessInvoiceItem::query();

        $this->startProgress(
            'Cleaning business invoice items...',
            $query->count()
        );

        if ($this->fastMode) {
            // No fast way to do this one
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (BusinessInvoiceItem $item) {
                if (strpos($item->group, ': ') > 0) {
                    // Remove names from groups
                    $item->group = substr($item->group, 0, strpos($item->group, ': ')) . ': ' . $this->faker->name() . ' - ' . $this->faker->name();
                }

                if (filled($item->notes)) {
                    $item->notes = $this->faker->sentence;
                }

                if ($item->isDirty()) {
                    $item->save();
                }

                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanCaregiverInvoiceItems()
    {
        $query = CaregiverInvoiceItem::query();

        $this->startProgress(
            'Cleaning caregiver invoice items...',
            $query->count()
        );

        if ($this->fastMode) {
            // No fast way to do this one
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (CaregiverInvoiceItem $item) {
                if (strpos($item->group, ': ') > 0) {
                    // Remove names from groups
                    $item->group = substr($item->group, 0, strpos($item->group, ': ')) . ': ' . $this->faker->name();
                }

                if ($item->isDirty()) {
                    $item->save();
                }

                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClientInvoices()
    {
        $query = ClientInvoice::whereNotNull('notes');

        $this->startProgress(
            'Cleaning client invoices...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update(['notes' => $this->faker->sentence]);
            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function ($item) {
                $item->notes = $this->faker->sentence;
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClientInvoiceItems()
    {
        $query = ClientInvoiceItem::query();

        $this->startProgress(
            'Cleaning client invoice items...',
            $query->count()
        );

        if ($this->fastMode) {
            // No fast way to do this one
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (ClientInvoiceItem $item) {
                if (strpos($item->group, ': ') > 0) {
                    // Remove names from groups
                    $item->group = substr($item->group, 0, strpos($item->group, ': ')) . ': ' . $this->faker->name();
                }

                if ($item->isDirty()) {
                    $item->save();
                }

                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanEncryptedClaimsData()
    {
        $query = ClaimableService::whereNotNull('caregiver_ssn');

        $this->startProgress(
            'Cleaning encrypted claims data...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'caregiver_ssn' => Crypt::encrypt($this->generateSsn()),
            ]);

            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (ClaimableService $item) {
                $item->caregiver_ssn = $this->generateSsn();
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClaimableServices()
    {
        $query = ClaimableService::query();
        $this->startProgress(
            'Cleaning claimable services...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'caregiver_last_name' => $this->faker->lastName,
                'caregiver_dob' => $this->faker->date('Y-m-d', '-30 years'),
                'caregiver_medicaid_id' => $this->faker->randomNumber(8),
                'address1' => $this->faker->streetAddress,
                'latitude' => $this->faker->latitude,
                'longitude' => $this->faker->longitude,
                'checked_in_number' => $this->generatePhoneNumber(),
                'checked_out_number' => $this->generatePhoneNumber(),
                'checked_in_latitude' => $this->faker->latitude,
                'checked_in_longitude' => $this->faker->longitude,
                'checked_out_latitude' => $this->faker->latitude,
                'checked_out_longitude' => $this->faker->longitude,
                'caregiver_comments' => $this->faker->sentence,
            ]);

            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function ($item) {
                $data = [
                    'caregiver_last_name' => $this->faker->lastName,
                    'caregiver_dob' => $this->faker->date('Y-m-d', '-30 years'),
                    'caregiver_medicaid_id' => $this->faker->randomNumber(8),
                    'address1' => $this->faker->streetAddress,
                    'latitude' => $this->faker->latitude,
                    'longitude' => $this->faker->longitude,
                ];

                if (filled($item->checked_in_number)) {
                    $data['checked_in_number'] = $this->generatePhoneNumber();
                }

                if (filled($item->checked_out_number)) {
                    $data['checked_out_number'] = $this->generatePhoneNumber();
                }

                if (filled($item->checked_in_latitude)) {
                    $data['checked_in_latitude'] = $this->faker->latitude;
                }

                if (filled($item->checked_in_longitude)) {
                    $data['checked_in_longitude'] = $this->faker->longitude;
                }

                if (filled($item->checked_out_latitude)) {
                    $data['checked_out_latitude'] = $this->faker->latitude;
                }

                if (filled($item->checked_out_longitude)) {
                    $data['checked_out_longitude'] = $this->faker->longitude;
                }

                if (filled($item->caregiver_comments)) {
                    $data['caregiver_comments'] = $this->faker->sentence;
                }

                $item->update($data);
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
                'ssn' => Crypt::encrypt($this->generateSsn()),
            ]);

            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (Caregiver $caregiver) {
                if ($caregiver->getOriginal('ssn')) {
                    $caregiver->ssn = $this->generateSsn();
                }
                $caregiver->save();
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
                'ssn' => Crypt::encrypt($this->generateSsn()),
            ]);

            $this->finish();
            return;
        }

        $query->chunk(400, function ($collection) {
            \DB::beginTransaction();
            $collection->each(function (Client $client) {
                if ($client->getOriginal('ssn')) {
                    $client->ssn = $this->generateSsn();
                    $client->save();
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

    /**
     * Generate a 10 digit phone number.
     *
     * @return string
     */
    protected function generatePhoneNumber() : string
    {
        return (string) mt_rand(3000000000, 9999999999);
    }

    /**
     * Generate a SSN.
     *
     * @return string
     */
    protected function generateSsn() : string
    {
        return mt_rand(100, 999) . '-' . mt_rand(10, 99) . '-' . mt_rand(1000, 9999);
    }
}
