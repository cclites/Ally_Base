<?php

namespace App\Console\Commands;

use App\BankAccount;
use App\Caregiver;
use App\CaregiverApplication;
use App\Client;
use App\CreditCard;
use App\EmergencyContact;
use App\Note;
use App\PhoneNumber;
use App\ScheduleNote;
use App\Shift;
use App\SmsThreadRecipient;
use App\SmsThreadReply;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class ClearSensitiveData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:sensitive_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear sensitive data from a production dump';

    /**
     * @var \Faker\Generator
     */
    protected $faker;

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

        $this->output->writeln("Setting a new application key..\n");
        $this->call('key:generate');

        // Reset admin password
        User::whereEmail('admin@allyms.com')->first()->changePassword('admin');

        // Reset lastname, birthdays, SSN on all clients and caregivers
        Client::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function($user) {
                $this->clearPersonalData($user);
            });
            \DB::commit();
        });
        Caregiver::chunk(400, function($collection) {
            \DB::beginTransaction();
            $collection->each(function($user) {
                $this->clearPersonalData($user);
            });
            \DB::commit();
        });
        CaregiverApplication::chunk(200, function($collection) {
            \DB::beginTransaction();
            $collection->each(function($user) {
                $this->clearPersonalData($user);
            });
            \DB::commit();
        });

        // Clear emergency contact name and numbers
        EmergencyContact::chunk(500, function($collection) {
            \DB::beginTransaction();
            $collection->each(function($contact) {
                $contact->update(['name' => $this->faker->name, 'phone_number' => mt_rand(3000000000,9999999999)]);
            });
            \DB::commit();
        });

        // Reset all credit card numbers
        CreditCard::chunk(500, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(CreditCard $card) {
                $card->name_on_card = $this->faker->name;
                $card->number = $this->faker->creditCardNumber;
                $card->save();
            });
            \DB::commit();
        });

        // Reset all bank account numbers
        BankAccount::chunk(500, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(BankAccount $account) {
                $account->name_on_account = $this->faker->name;
                $account->account_number = $this->faker->bankAccountNumber;
                $account->save();
            });
            \DB::commit();
        });

        // Reset all SMS numbers
        SmsThreadRecipient::select('user_id')->groupBy('user_id')->get()->each(function(SmsThreadRecipient $recipient) {
            $phoneNumber = PhoneNumber::where('user_id', $recipient->user_id)->first();
            $newNumber = $phoneNumber->national_number;
            SmsThreadRecipient::where('user_id', $recipient->user_id)->update(['number' => $newNumber]);
            SmsThreadReply::where('user_id', $recipient->user_id)->update(['from_number' => $newNumber]);
        });

        // Reset all notes
        Note::chunk(500, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(Note $note) {
                $note->body = $this->faker->paragraph;
                $note->save();
            });
            \DB::commit();
        });
        ScheduleNote::chunk(500, function($collection) {
            \DB::beginTransaction();
            $collection->each(function(ScheduleNote $note) {
                $note->note = $this->faker->sentence;
                $note->save();
            });
            \DB::commit();
        });

        // Clear shift data
        \DB::statement('UPDATE shifts SET caregiver_comments = ? WHERE caregiver_comments IS NOT NULL', [$this->faker->sentence]);
        \DB::statement('UPDATE shifts SET checked_in_latitude = checked_in_latitude + RAND(), checked_out_latitude = checked_out_latitude + RAND() WHERE checked_in_latitude IS NOT NULL');
        \DB::statement('UPDATE shifts SET checked_in_number = "555555555", checked_out_number = "555555555" WHERE checked_in_number IS NOT NULL');
    }

    protected function clearPersonalData(Model $user)
    {
        if ($user->getOriginal('ssn')) {
            $user->ssn = mt_rand(100,999) . '-' . mt_rand(10,99) . '-' . mt_rand(1000,9999);
        }
        if ($user->date_of_birth) {
            $user->date_of_birth = $this->faker->date('Y-m-d', '-30 years');
        }
        if ($user->lastname) {
            $user->lastname = $this->faker->lastName;
        }
        else if ($user->last_name) {
            // for applications
            $user->last_name = $this->faker->lastName;
            $user->address = $this->faker->streetAddress;
        }
        if ($user->addresses) {
            foreach($user->addresses as $address) {
                $address->address1 = $this->faker->streetAddress;
                $address->latitude = $address->latitude + (float) rand() / (float) getrandmax();
                $address->longitude = $address->longitude - (float) rand() / (float) getrandmax();
                $address->save();
            }
        }
        if ($user->phoneNumbers) {
            foreach($user->phoneNumbers as $number) {
                $number->update(['national_number' => mt_rand(3000000000,9999999999)]);
            }
        }

        $user->save();
    }
}
