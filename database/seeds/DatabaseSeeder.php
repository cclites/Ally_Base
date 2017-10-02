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

        $client = factory(\App\Client::class)->create([
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
        $business->caregivers()->attach($caregiver, ['default_rate' => 20.00]);

        // Create Others
        factory(\App\Business::class, 3)->create();
        factory(\App\Client::class, 20)->create();
        factory(\App\Caregiver::class, 10)->create();
        factory(\App\OfficeUser::class, 6)->create();

        // Create shift and payment entries
        factory(\App\Shift::class, 50)->create([
            'caregiver_id' => $caregiver->id,
            'client_id' => $client->id,
            'business_id' => $business->id
        ])->each(function($shift) {
            $secondsSince = time() - strtotime($shift->checked_out_time);
            if ($secondsSince < 0) return;
            $duration = round((strtotime($shift->checked_out_time) - strtotime($shift->checked_in_time)) / 3600);
            $amount = $duration * 20.00;
            $business_fee = $duration * 4.00;
            $system_fee = $amount * 0.05;
            if ($secondsSince < (86400*3)) {
                $queue = \App\PaymentQueue::create([
                    'client_id' => $shift->client_id,
                    'business_id' => $shift->business_id,
                    'reference_type' => \App\Shift::class,
                    'reference_id' => $shift->id,
                    'amount' => $amount,
                    'business_allotment' => $business_fee,
                    'system_allotment' => $system_fee,
                    'caregiver_allotment' => $amount - ($business_fee + $system_fee),
                    'created_at' => $shift->checked_out_time,
                    'updated_at' => $shift->checked_out_time,
                ]);
            }
            else {
                $payment = \App\Payment::create([
                    'client_id' => $shift->client_id,
                    'business_id' => $shift->business_id,
                    'reference_type' => \App\Shift::class,
                    'reference_id' => $shift->id,
                    'amount' => $amount,
                    'business_allotment' => $business_fee,
                    'system_allotment' => $system_fee,
                    'caregiver_allotment' => $amount - ($business_fee + $system_fee),
                    'success' => 1,
                    'created_at' => $shift->checked_out_time,
                    'updated_at' => $shift->checked_out_time,
                ]);
            }
        });

        // Batch deposits
        $date = new DateTime('monday 3 months ago');
        while($date->format('U') < time()) {
            $start = $date->format('Y-m-d') . ' 00:00:00';
            $date->add(new \DateInterval('P1W'));
            $end = $date->format('Y-m-d') . ' 23:59:59';

            \DB::beginTransaction();
            $businessDeposit = \App\Deposit::create([
                'deposit_type' => 'business',
                'business_id' => $business->id,
                'amount' => 0.00,
                'created_at' => $end,
            ]);
            $caregiverDeposit = \App\Deposit::create([
                'deposit_type' => 'caregiver',
                'business_id' => $caregiver->id,
                'amount' => 0.00,
                'created_at' => $end,
            ]);

            $payments = \App\Payment::where('deposited', 0)
                ->whereBetween('created_at', [$start, $end])->each(function (\App\Payment $payment) use ($businessDeposit, $caregiverDeposit) {
                    $businessDeposit->amount += $payment->business_allotment;
                    $caregiverDeposit->amount += $payment->caregiver_allotment;
                    $businessDeposit->payments()->attach($payment);
                    $caregiverDeposit->payments()->attach($payment);
                    $businessDeposit->save();
                    $caregiverDeposit->save();
                    $payment->update(['deposited' => true]);
                });

            if ($businessDeposit->amount == 0) $businessDeposit->delete();
            if ($caregiverDeposit->amount == 0) $caregiverDeposit->delete();

            \DB::commit();
        }
    }
}
