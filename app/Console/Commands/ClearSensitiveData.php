<?php

namespace App\Console\Commands;

use App\BankAccount;
use App\Caregiver;
use App\Client;
use App\CreditCard;
use App\User;
use Faker\Factory;
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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->faker = Factory::create();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (env('APP_ENV') === 'production') {
            exit('This command cannot be run in production.');
        }

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

    protected function clearPersonalData(Model $user) {
        $attributes = $user->getAttributes();
        if (!empty($attributes['ssn'])) {
            $user->ssn = mt_rand(100,999) . '-' . mt_rand(10,99) . '-' . mt_rand(1000,9999);
        }
        if ($user->date_of_birth) {
            $user->date_of_birth = $this->faker->date('Y-m-d', '-30 years');
        }
        if ($user->lastname) {
            $user->lastname = $this->faker->lastName;
        }
        foreach($user->addresses as $address) {
            $address->address1 = $this->faker->streetAddress;
            $address->save();
        }
        $user->save();
    }
}
