<?php

use App\Activity;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Deposit;
use App\OfficeUser;
use App\Payment;
use App\Schedule;
use App\Shift;
use App\ShiftActivity;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = factory(\App\Admin::class)->create([
            'firstname' => 'Demo',
            'lastname' => 'Admin',
            'email' => 'admin@allyms.com',
            'username' => 'admin@allyms.com',
            'password' => bcrypt('demo'),
        ]);

        $business = factory(Business::class)->create([
            'name' => 'Ally Demo Business'
        ]);

        $client = factory(Client::class)->create([
            'firstname' => 'Demo',
            'lastname' => 'Client',
            'email' => 'client@allyms.com',
            'username' => 'client@allyms.com',
            'password' => bcrypt('demo'),
            'business_id' => $business->id
        ]);

        $officeUser = factory(OfficeUser::class)->create([
            'firstname' => 'Demo',
            'lastname' => 'User',
            'email' => 'officeuser@allyms.com',
            'username' => 'officeuser@allyms.com',
            'password' => bcrypt('demo')
        ]);
        $business->users()->attach($officeUser);

        $caregiver = factory(Caregiver::class)->create([
            'firstname' => 'Demo',
            'lastname' => 'Caregiver',
            'email' => 'caregiver@allyms.com',
            'username' => 'caregiver@allyms.com',
            'password' => bcrypt('demo')
        ]);
        $business->caregivers()->attach($caregiver, ['default_rate' => 20.00]);

        // Create Other Users
        factory(Business::class, 3)->create();
        factory(Client::class, 30)->create();
        factory(Caregiver::class, 20)->create()->each(function($user) {
            Business::inRandomOrder()->first()->caregivers()->attach($user);
        });
        factory(OfficeUser::class, 6)->create()->each(function($user) {
            Business::inRandomOrder()->first()->users()->attach($user);
        });

        // Attach phone numbers and addresses
        User::all()->each(function (User $user) {
            $user->phoneNumbers()->save(factory(\App\PhoneNumber::class)->make());
            $user->addresses()->save(factory(\App\Address::class)->make());
        });

        // Create Activities
        factory(Activity::class, 15)->create();

        // Create schedules
        factory(Schedule::class, 1000)->create();

        // Create some payment-less shifts
        factory(Shift::class, 30)->create(['status' => 'WAITING_FOR_AUTHORIZATION']);

        // Create some payments
        $payments = factory(Payment::class, 20)->create();

        // Create some charged shifts
        factory(Shift::class, 40)->create()->each(function(Shift $shift) use ($payments) {
            $shift->status = 'WAITING_FOR_PAYOUT';
            $shift->payment_id = $payments->shuffle()->first()->id;
            $shift->save();
        });

        // Create some deposits
        $deposits = factory(Deposit::class, 20)->create();

        // Create some fully paid shifts
        factory(Shift::class, 60)->create()->each(function(Shift $shift) use ($deposits) {
            $shift->status = 'PAID';
            $shift->save();
            $deposits->shuffle()->first()->shifts()->attach($shift);
        });

        $this->call([
            ProspectSeeder::class,
            FranchisorSeeder::class,
        ]);
    }
}
