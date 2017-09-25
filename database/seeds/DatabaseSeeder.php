<?php

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
        $business = factory(\App\Business::class)->create([
            'name' => 'Ally Demo Business'
        ]);

        factory(\App\Client::class)->create([
            'firstname' => 'Demo',
            'lastname' => 'Client',
            'email' => 'client@allyms.com',
            'password' => bcrypt('demo'),
            'business_id' => $business->id
        ]);

        $officeUser = factory(\App\OfficeUser::class)->create([
            'firstname' => 'Demo',
            'lastname' => 'User',
            'email' => 'officeuser@allyms.com',
            'password' => bcrypt('demo')
        ]);
        $business->users()->attach($officeUser);

        $caregiver = factory(\App\Caregiver::class)->create([
            'firstname' => 'Demo',
            'lastname' => 'Caregiver',
            'email' => 'caregiver@allyms.com',
            'password' => bcrypt('demo')
        ]);
        $business->caregivers()->attach($caregiver);

        // Create Others
        factory(\App\Business::class, 3)->create();
        factory(\App\Client::class, 20)->create();
        factory(\App\Caregiver::class, 10)->create();
        factory(\App\OfficeUser::class, 6)->create();
    }
}
