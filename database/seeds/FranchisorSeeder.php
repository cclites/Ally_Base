<?php

use Illuminate\Database\Seeder;

class FranchisorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $business = factory(\App\Business::class)->create([
            'name' => 'Ally Demo Franchisor',
            'type' => \App\Business::TYPE_FRANCHISOR,
        ]);

        $user = factory(\App\OfficeUser::class)->create([
            'firstname' => 'Demo',
            'lastname' => 'Franchisor',
            'email' => 'franchisor@allyms.com',
            'username' => 'franchisor@allyms.com',
            'password' => bcrypt('demo'),
        ]);

        $business->users()->attach($user);
    }
}
