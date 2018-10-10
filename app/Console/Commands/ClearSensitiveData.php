<?php

namespace App\Console\Commands;

use App\BankAccount;
use App\Caregiver;
use App\CaregiverApplication;
use App\Client;
use App\CreditCard;
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
        Client::chunk(200, function($collection) {
            $collection->each(function($user) {
                $this->clearPersonalData($user);
            });
        });
        Caregiver::chunk(200, function($collection) {
            $collection->each(function($user) {
                $this->clearPersonalData($user);
            });
        });
        CaregiverApplication::chunk(200, function($collection) {
            $collection->each(function($user) {
                $this->clearPersonalData($user);
            });
        });

        // Reset all credit card numbers
        CreditCard::chunk(200, function($collection) {
            $collection->each(function(CreditCard $card) {
                $card->name_on_card = $this->faker->name;
                $card->number = $this->faker->creditCardNumber;
                $card->save();
            });
        });

        // Reset all bank account numbers
        BankAccount::chunk(200, function($collection) {
            $collection->each(function(BankAccount $account) {
                $account->name_on_account = $this->faker->name;
                $account->account_number = $this->faker->bankAccountNumber;
                $account->save();
            });
        });
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
        }
        if ($user->addresses) {
            foreach($user->addresses as $address) {
                $address->address1 = $this->faker->streetAddress;
                $address->save();
            }
        }

        $user->save();
    }
}
