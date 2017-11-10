<?php

use App\Activity;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Payment;
use App\ShiftActivity;
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

        $this->generateActivities($business);

        $client = factory(Client::class)->create([
            'firstname' => 'Demo',
            'lastname' => 'Client',
            'email' => 'client@allyms.com',
            'username' => 'client@allyms.com',
            'password' => bcrypt('demo'),
            'business_id' => $business->id
        ]);

        $officeUser = factory(\App\OfficeUser::class)->create([
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

        $this->generateShifts($caregiver, $client, $business);

        // Create Others
        factory(Business::class, 3)->create();
        factory(Client::class, 20)->create();
        factory(Caregiver::class, 10)->create();
        factory(\App\OfficeUser::class, 6)->create();

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

            $payments = Payment::where('deposited', 0)
                ->whereBetween('created_at', [$start, $end])->each(function (Payment $payment) use ($businessDeposit, $caregiverDeposit) {
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

    private function generateShifts(Caregiver $caregiver, Client $client, Business $business)
    {
        for ($i = 1; $i < 6; $i++) {
            $start = Carbon::now()->subWeeks($i + 1);
            $end = $start->copy()->addHours(2);
            // Create shift and payment entries
            $data = [
                'caregiver_id' => $caregiver->id,
                'client_id' => $client->id,
                'business_id' => $business->id,
                'checked_in_time' => $start,
                'checked_out_time' => $end
            ];

            $shifts = factory(\App\Shift::class, 10)->create($data);

            $duration = $shifts->reduce(function ($carry, $shift) {
                return $carry + round((strtotime($shift->checked_out_time) - strtotime($shift->checked_in_time)) / 3600);
            });

            $amount = $duration * 20.00;
            $business_fee = $duration * 4.00;
            $system_fee = $amount * 0.05;

            $payment = Payment::create([
                'client_id' => $shifts->first()->client_id,
                'business_id' => $shifts->first()->business_id,
                'amount' => $amount,
                'business_allotment' => $business_fee,
                'system_allotment' => $system_fee,
                'caregiver_allotment' => $amount - ($business_fee + $system_fee),
                'success' => 1
            ]);

            $shifts->each(function($shift) use ($payment) {
                ShiftActivity::create(['shift_id' => $shift->id, 'activity_id' => rand(1, 7)]);
                $shift->payment_id = $payment->id;
                $shift->save();
            });
        }
    }

    private function generateActivities(Business $business)
    {
        // Demo activities
        $activities = [
            'Bathing - Shower',
            'Bathing - Bed',
            'Shave',
            'Mouth Care',
            'Incontinence Care',
            'Medication Reminders',
            'Turning',
            'Feeding',
        ];
        for ($i = 0; $i < count($activities)-1; $i++) {
            Activity::create([
                'code' => str_pad($i+1, 3, 0,STR_PAD_LEFT),
                'name' => $activities[$i],
                'business_id' => $business->id,
            ]);
        }
    }
}
