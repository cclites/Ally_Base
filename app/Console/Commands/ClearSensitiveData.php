<?php

namespace App\Console\Commands;

use App\Address;
use App\Audit;
use App\Billing\Payments\Methods\BankAccount;
use App\Caregiver;
use App\CaregiverApplication;
use App\Claims\ClaimableExpense;
use App\Claims\ClaimableService;
use App\Claims\ClaimAdjustment;
use App\Claims\ClaimInvoice;
use App\Claims\ClaimRemit;
use App\Client;
use App\Billing\Payments\Methods\CreditCard;
use App\ClientMedication;
use App\CommunicationLog;
use App\EmergencyContact;
use App\Note;
use App\PhoneNumber;
use App\ScheduleNote;
use App\SmsThreadRecipient;
use App\SmsThreadReply;
use App\Traits\Console\HasProgressBars;
use App\User;
use Crypt;
use Illuminate\Console\Command;
use App\Business;
use App\ClientContact;
use App\QuickbooksConnection;

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

        // Instantiate the faker library for dummy data
        // Do not instantiate this in the constructor since production does not have this library
        $this->faker = \Faker\Factory::create();

        if ($this->option('reset-key')) {
            $this->info("Setting a new application key...");
            $this->call('key:generate');
        }

        if ($this->option('fast')) {
            $this->fastMode = true;
        }

        // Fix encryption
        $this->clearAuditLog();
        $this->clearCommunicationsLog();
        $this->cleanCaregivers();
        $this->cleanCaregiverApplications();
        $this->cleanClients();
        $this->cleanClientMedication();
        $this->clean3rdPartyCredentials();
        $this->cleanFinancialAccounts();
        $this->cleanEncryptedClaimsData();

        if (! $this->option('fix-only')) {
            // Only execute these options if fix-only if OFF
            $this->cleanUserData();
            $this->cleanAddresses();
            $this->cleanPhoneNumbers();
            $this->cleanEmergencyContacts();
            $this->cleanClientContacts();
            $this->cleanNotes();
            $this->cleanShifts();
            $this->cleanSmsData();
            $this->cleanClaimData();
        }

        $this->fixDemoAccounts($this->argument('password'));

        $this->info('Success.');

        return 0;
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

        $query->chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(ClaimableService $item) {
                $item->caregiver_ssn = $this->generateSsn();
                $item->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClaimData()
    {
        $this->cleanClaimInvoices();
        $this->cleanClaimableServices();
        $this->cleanClaimableExpenses();
        $this->cleanClaimRemits();
        $this->cleanClaimAdjustments();
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

        $query->chunk(400, function($collection) {
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

    public function cleanClaimInvoices()
    {
        $query = ClaimInvoice::query();
        $this->startProgress(
            'Cleaning claim invoices...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'client_last_name' => $this->faker->lastName,
                'client_dob' => $this->faker->date('Y-m-d', '-30 years'),
                'client_medicaid_id' => $this->faker->randomNumber(8),
            ]);

            $this->finish();
            return;
        }

        $query->chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function ($item) {
                $item->update([
                    'client_last_name' => $this->faker->lastName,
                    'client_dob' => $this->faker->date('Y-m-d', '-30 years'),
                    'client_medicaid_id' => $this->faker->randomNumber(8),
                ]);
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClaimableExpenses()
    {
        $query = ClaimableExpense::query();
        $this->startProgress(
            'Cleaning claimable expenses...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'caregiver_last_name' => $this->faker->lastName,
                'notes' => $this->faker->sentence,
            ]);

            $this->finish();
            return;
        }

        $query->chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function ($item) {
                $data = [
                    'caregiver_last_name' => $this->faker->lastName,
                ];
                if (isset($item->notes)) {
                    $data['notes'] = $this->faker->sentence;
                }
                $item->update($data);
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClaimRemits()
    {
        $query = ClaimRemit::whereNotNull('notes');
        $this->startProgress(
            'Cleaning claim remits...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'notes' => $this->faker->sentence,
            ]);

            $this->finish();
            return;
        }

        $query->chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function ($item) {
                $item->update(['notes' => $this->faker->sentence]);
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClaimAdjustments()
    {
        $query = ClaimAdjustment::whereNotNull('note');
        $this->startProgress(
            'Cleaning claim adjustments...',
            $query->count()
        );

        if ($this->fastMode) {
            $query->update([
                'note' => $this->faker->sentence,
            ]);

            $this->finish();
            return;
        }

        $query->chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function ($item) {
                $item->update(['note' => $this->faker->sentence]);
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanPhoneNumbers()
    {
        $this->startProgress(
            'Cleaning phone numbers...',
            PhoneNumber::count()
        );

        if ($this->fastMode) {
            PhoneNumber::whereRaw(1)->update([
                'national_number' => $this->generatePhoneNumber(),
            ]);

            $this->finish();
            return;
        }

        PhoneNumber::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function ($item) {
                $item->update(['national_number' => $this->generatePhoneNumber()]);

                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanAddresses()
    {
        $this->startProgress(
            'Cleaning addresses...',
            Address::count()
        );

        if ($this->fastMode) {
            Address::whereRaw(1)->update([
                'address1' => $this->faker->streetAddress,
                'latitude' => $this->faker->latitude,
                'longitude' => $this->faker->longitude,
            ]);

            $this->finish();
            return;
        }

        Address::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function ($item) {
                $item->address1 = $this->faker->streetAddress;
                $item->latitude = $item->latitude + (float) rand() / (float) getrandmax();
                $item->longitude = $item->longitude - (float) rand() / (float) getrandmax();
                $item->save();

                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function clearCommunicationsLog()
    {
        $this->startProgress(
            'Clearing communications log...',
            1
        );

        CommunicationLog::truncate();

        $this->finish();
    }

    public function clearAuditLog()
    {
        $this->startProgress(
            'Clearing audit log...',
            1
        );

        Audit::truncate();

        $this->finish();
    }

    public function cleanClientMedication()
    {
        $this->startProgress(
            'Cleaning Client medication records...',
            ClientMedication::count()
        );

        if ($this->fastMode) {
            ClientMedication::whereRaw(1)->update([
                'type' => Crypt::encrypt($this->faker->sentence),
                'dose' => Crypt::encrypt($this->faker->sentence),
                'frequency' => Crypt::encrypt($this->faker->randomDigit),
                'description' => Crypt::encrypt($this->faker->sentence),
                'side_effects' => Crypt::encrypt($this->faker->sentence),
                'notes' => Crypt::encrypt($this->faker->sentence),
                'tracking' => Crypt::encrypt($this->faker->sentence),
            ]);

            $this->finish();
            return;
        }

        ClientMedication::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function ($item) {
                $item->type = $this->faker->sentence;
                $item->dose = $this->faker->sentence;
                $item->frequency = $this->faker->randomDigit;
                $item->description = $this->faker->sentence;
                $item->side_effects = $this->faker->sentence;
                $item->notes = $this->faker->sentence;
                $item->tracking = $this->faker->sentence;
                $item->save();

                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanUserData()
    {
        $this->startProgress(
            'Cleaning personal user data...',
            User::count()
        );

        if ($this->fastMode) {
            User::where('role_type', '<>', 'admin')->whereRaw(1)->update([
                'date_of_birth' => $this->faker->date('Y-m-d', '-30 years'),
                'lastname' => 'User',
                'username' => \DB::raw("CONCAT('user', id)"),
                'email' => \DB::raw("CONCAT('user', id, '@test.com')"),
                'notification_email' => \DB::raw("CONCAT('user', id, '@test.com')"),
                'notification_phone' => $this->faker->phoneNumber,
                'remember_token' => null,
            ]);

            $this->finish();
            return;
        }

        User::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function($user) {
                if ($user->date_of_birth) {
                    $user->date_of_birth = $this->faker->date('Y-m-d', '-30 years');
                }
                if ($user->lastname) {
                    $user->lastname = $this->faker->lastName;
                }
                if (in_array($user->role_type, ['caregiver', 'client'])) {
                    $user->email = $this->faker->email;
                }
                if ($user->notification_email) {
                    $user->notification_email = $user->email ? $user->email :  $this->faker->email;
                }
                if ($user->notification_phone) {
                    $user->notification_phone = $this->faker->phoneNumber;
                }
                if ($user->remember_token) {
                    $user->remember_token = null;
                }
                $user->save();

                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanCaregivers()
    {
        $this->startProgress(
            'Cleaning Caregiver records...',
            Caregiver::count()
        );

        if ($this->fastMode) {
            Caregiver::whereRaw(1)->update([
                'ssn' => Crypt::encrypt($this->generateSsn()),
            ]);

            $this->finish();
            return;
        }

        Caregiver::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(Caregiver $caregiver) {
                if (! $caregiver->user) {
                    $this->advance();
                    return;
                }
                if ($caregiver->getOriginal('ssn')) {
                    $caregiver->ssn = $this->generateSsn();
                    $caregiver->save();
                }
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClients()
    {
        $this->startProgress(
            'Cleaning Client records...',
            Client::count()
        );

        if ($this->fastMode) {
            Client::whereRaw(1)->update([
                'ssn' => Crypt::encrypt($this->generateSsn()),
            ]);

            $this->finish();
            return;
        }

        Client::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(Client $client) {
                if (! $client->user) {
                    $this->advance();
                    return;
                }
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

    public function cleanCaregiverApplications()
    {
        $this->startProgress(
            'Cleaning Caregiver applications...',
            CaregiverApplication::count()
        );

        if ($this->fastMode) {
            CaregiverApplication::whereRaw(1)->update([
                'date_of_birth' => $this->faker->date('Y-m-d', '-30 years'),
                'last_name' => 'User',
                'email' => \DB::raw("CONCAT('user', id, '@test.com')"),
                'ssn' => Crypt::encrypt($this->generateSsn()),
                'address' => $this->faker->streetAddress,
                'cell_phone' => $this->generatePhoneNumber(),
                'home_phone' => $this->generatePhoneNumber(),
                'emergency_contact_name' => $this->faker->name,
                'emergency_contact_phone' => $this->generatePhoneNumber(),
                'driving_violations_desc' => $this->faker->sentence,
                'criminal_history_desc' => $this->faker->sentence,
                'injury_status_desc' => $this->faker->sentence,
                'employer_1_name' => $this->faker->company,
                'employer_1_phone' => $this->generatePhoneNumber(),
                'employer_1_supervisor_name' => $this->faker->firstName,
                'employer_2_name' => $this->faker->company,
                'employer_2_phone' => $this->generatePhoneNumber(),
                'employer_2_supervisor_name' => $this->faker->firstName,
                'employer_3_name' => $this->faker->company,
                'employer_3_phone' => $this->generatePhoneNumber(),
                'employer_3_supervisor_name' => $this->faker->firstName,
                'reference_1_name' => $this->faker->name,
                'reference_1_phone' => $this->generatePhoneNumber(),
                'reference_2_name' => $this->faker->name,
                'reference_2_phone' => $this->generatePhoneNumber(),
                'reference_3_name' => $this->faker->name,
                'reference_3_phone' => $this->generatePhoneNumber(),
            ]);
            $this->finish();
            return;
        }

        CaregiverApplication::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(CaregiverApplication $application) {
                if ($application->date_of_birth) {
                    $application->date_of_birth = $this->faker->date('Y-m-d', '-30 years');
                }
                if ($application->last_name) {
                    $application->last_name = $this->faker->lastName;
                }
                $application->email = $this->faker->email;
                if ($application->getOriginal('ssn')) {
                    $application->ssn = $this->generateSsn();
                }
                if ($application->address) {
                    $application->address = $this->faker->streetAddress;
                }
                if ($application->cell_phone) {
                    $application->cell_phone = $this->generatePhoneNumber();
                }
                if ($application->home_phone) {
                    $application->home_phone = $this->generatePhoneNumber();
                }
                if ($application->emergency_contact_name) {
                    $application->emergency_contact_name = $this->faker->name;
                }
                if ($application->emergency_contact_phone) {
                    $application->emergency_contact_phone = $this->generatePhoneNumber();
                }
                if ($application->driving_violations_desc) {
                    $application->driving_violations_desc = $this->faker->sentence;
                }
                if ($application->criminal_history_desc) {
                    $application->criminal_history_desc = $this->faker->sentence;
                }
                if ($application->injury_status_desc) {
                    $application->injury_status_desc = $this->faker->sentence;
                }
                if ($application->employer_1_name) {
                    $application->employer_1_name = $this->faker->company;
                }
                if ($application->employer_1_phone) {
                    $application->employer_1_phone = $this->generatePhoneNumber();
                }
                if ($application->employer_1_supervisor_name) {
                    $application->employer_1_supervisor_name = $this->faker->firstName;
                }
                if ($application->employer_2_name) {
                    $application->employer_2_name = $this->faker->company;
                }
                if ($application->employer_2_phone) {
                    $application->employer_2_phone = $this->generatePhoneNumber();
                }
                if ($application->employer_2_supervisor_name) {
                    $application->employer_2_supervisor_name = $this->faker->firstName;
                }
                if ($application->employer_3_name) {
                    $application->employer_3_name = $this->faker->company;
                }
                if ($application->employer_3_phone) {
                    $application->employer_3_phone = $this->generatePhoneNumber();
                }
                if ($application->employer_3_supervisor_name) {
                    $application->employer_3_supervisor_name = $this->faker->firstName;
                }
                if ($application->reference_1_name) {
                    $application->reference_1_name = $this->faker->name;
                }
                if ($application->reference_1_phone) {
                    $application->reference_1_phone = $this->generatePhoneNumber();
                }
                if ($application->reference_2_name) {
                    $application->reference_2_name = $this->faker->name;
                }
                if ($application->reference_2_phone) {
                    $application->reference_2_phone = $this->generatePhoneNumber();
                }
                if ($application->reference_3_name) {
                    $application->reference_3_name = $this->faker->name;
                }
                if ($application->reference_3_phone) {
                    $application->reference_3_phone = $this->generatePhoneNumber();
                }
                $application->save();

                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanClientContacts()
    {
        $this->startProgress(
            'Cleaning Client Contact records...',
            ClientContact::count()
        );

        if ($this->fastMode) {
            ClientContact::whereRaw(1)->update([
                'name' => $this->faker->name,
                'phone1' => $this->generatePhoneNumber(),
                'phone2' => $this->generatePhoneNumber(),
                'work_phone' => $this->generatePhoneNumber(),
                'fax_number' => $this->generatePhoneNumber(),
                'email' => $this->faker->email,
                'address' => $this->faker->streetAddress
            ]);

            $this->finish();
            return;
        }

        ClientContact::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(ClientContact $contact) {
                $contact->update([
                    'name' => $this->faker->name,
                    'phone1' => $this->generatePhoneNumber(),
                    'phone2' => $this->generatePhoneNumber(),
                    'work_phone' => $this->generatePhoneNumber(),
                    'fax_number' => $this->generatePhoneNumber(),
                    'email' => $this->faker->email,
                    'address' => $this->faker->streetAddress
                ]);
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanEmergencyContacts()
    {
        $this->startProgress(
            'Cleaning Emergency Contact records...',
            EmergencyContact::count()
        );

        if ($this->fastMode) {
            EmergencyContact::whereRaw(1)->update([
                'name' => $this->faker->name,
                'phone_number' => $this->generatePhoneNumber()
            ]);

            $this->finish();
            return;
        }

        EmergencyContact::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(EmergencyContact $contact) {
                $contact->update(['name' => $this->faker->name, 'phone_number' => $this->generatePhoneNumber()]);
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanFinancialAccounts() : void
    {
        $this->startProgress(
            'Cleaning financial account data...',
            CreditCard::count() + BankAccount::count()
        );

        if ($this->fastMode) {
            CreditCard::whereRaw(1)->update([
                'name_on_card' => $this->faker->name,
                'number' => Crypt::encrypt($this->faker->creditCardNumber),
            ]);

            BankAccount::whereRaw(1)->update([
                'name_on_account' => $this->faker->name,
                'routing_number' => Crypt::encrypt('091000019'),
                'account_number' => Crypt::encrypt($this->faker->bankAccountNumber),
            ]);

            $this->finish();
            return;
        }

        CreditCard::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(CreditCard $card) {
                $card->name_on_card = $this->faker->name;
                $card->number = $this->faker->creditCardNumber;
                $card->save();
                $this->advance();
            });
            \DB::commit();
        });

            BankAccount::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(BankAccount $account) {
                $account->name_on_account = $this->faker->name;
                $account->routing_number = "091000019";
                $account->account_number = $this->faker->bankAccountNumber;
                $account->save();
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanSmsData()
    {
        $this->startProgress(
            'Cleaning SMS data...',
            SmsThreadRecipient::select('user_id')->distinct()->count('user_id')
        );

        if ($this->fastMode) {
            SmsThreadRecipient::whereRaw(1)->update(['number' => $this->generatePhoneNumber()]);
            SmsThreadReply::whereRaw(1)->update(['from_number' => $this->generatePhoneNumber()]);

            $this->finish();
            return;
        }

        $recipients = SmsThreadRecipient::select('user_id')->groupBy('user_id');
        $recipients->get()->each(function(SmsThreadRecipient $recipient) {
            $number = PhoneNumber::where('user_id', $recipient->user_id)->first();
            $number = $number ? $number->national_number : $this->generatePhoneNumber();
            SmsThreadRecipient::where('user_id', $recipient->user_id)->update(['number' => $number]);
            SmsThreadReply::where('user_id', $recipient->user_id)->update(['from_number' => $number]);

            $this->advance();
        });

        $this->finish();
    }

    public function cleanNotes()
    {
        $this->startProgress(
            'Cleaning Notes...',
            Note::count() + ScheduleNote::count()
        );

        if ($this->fastMode) {
            Note::whereRaw(1)->update(['body' => $this->faker->sentence]);
            ScheduleNote::whereRaw(1)->update(['note' => $this->faker->sentence]);

            $this->finish();
            return;
        }

        Note::chunk(500, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(Note $note) {
                $note->update(['body' => $this->faker->sentence]);
                $this->advance();
            });
            \DB::commit();
        });
        ScheduleNote::chunk(500, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(ScheduleNote $note) {
                $note->update(['note' => $this->faker->sentence]);
                $this->advance();
            });
            \DB::commit();
        });

        $this->finish();
    }

    public function cleanShifts()
    {
        $this->startProgress(
            'Cleaning Shift EVV data...',
            3
        );

        \DB::statement('UPDATE shifts SET caregiver_comments = ? WHERE caregiver_comments IS NOT NULL', [$this->faker->sentence]);
        $this->advance();

        \DB::statement('UPDATE shifts SET checked_in_latitude = checked_in_latitude + RAND(), checked_out_latitude = checked_out_latitude + RAND() WHERE checked_in_latitude IS NOT NULL');
        $this->advance();

        \DB::statement('UPDATE shifts SET checked_in_number = "555555555", checked_out_number = "555555555" WHERE checked_in_number IS NOT NULL');
        $this->advance();

        // TODO: implement slow mode?

        $this->finish();
    }

    public function clean3rdPartyCredentials()
    {
        $this->startProgress(
            'Clearing 3rd party credentials...',
            Business::count()+1
        );

        // TODO: implement fast mode?

        // Clear HHA/Tellus credentials
        Business::chunk(200, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(Business $business) {
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

    public function fixDemoAccounts(string $password)
    {
        $this->startProgress(
            'Resetting demo account data...',
            5
        );

        // Change all user passwords
        User::whereRaw(1)->update(['password' => bcrypt($password)]);
        $this->advance();

        // reset demo users account info
        User::find(1)->update(['firstname' => 'Demo', 'lastname' => 'Client', 'username' => 'client@allyms.com']);
        $this->advance();

        User::find(2)->update(['firstname' => 'Demo', 'lastname' => 'User', 'username' => 'officeuser@allyms.com']);
        $this->advance();

        User::find(3)->update(['firstname' => 'Demo', 'lastname' => 'Caregiver', 'username' => 'caregiver@allyms.com']);
        $this->advance();

        User::whereEmail('admin@allyms.com')->update(['firstname' => 'Admin', 'lastname' => 'User', 'username' => 'admin@allyms.com']);
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
        return (string) mt_rand(3000000000,9999999999);
    }

    /**
     * Generate a SSN.
     *
     * @return string
     */
    protected function generateSsn() : string
    {
        return mt_rand(100,999) . '-' . mt_rand(10,99) . '-' . mt_rand(1000,9999);
    }
}
