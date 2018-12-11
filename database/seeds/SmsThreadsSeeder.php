<?php

use Illuminate\Database\Seeder;
use App\OfficeUser;
use App\SmsThread;
use App\SmsThreadRecipient;
use App\SmsThreadReply;

class SmsThreadsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $officeUser = OfficeUser::findOrFail(2);

        $business = $officeUser->businesses()->first();

        $caregivers = $business->caregivers()
            ->has('phoneNumbers')
            ->with('phoneNumbers')
            ->get();

        $thread = factory(SmsThread::class)->create([
            'business_id' => $business->id,
        ]);

        foreach ($caregivers as $recipient) {
            SmsThreadRecipient::create([
                'sms_thread_id' => $thread->id,
                'user_id' => $recipient->id,
                'number' => $recipient->phoneNumber->national_number,
            ]);

            factory(SmsThreadReply::class)->create([
                'sms_thread_id' => $thread->id,
                'user_id' => $recipient->id,
                'from_number' => $recipient->phoneNumber->national_number,
                'business_id' => $business->id,
            ]);

            factory(SmsThreadReply::class)->create([
                'sms_thread_id' => null,
                'user_id' => $recipient->id,
                'from_number' => $recipient->phoneNumber->national_number,
                'business_id' => $business->id,
            ]);
        }
    }
}
