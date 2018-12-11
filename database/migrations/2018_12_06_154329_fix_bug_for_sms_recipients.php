<?php

use App\PhoneNumber;
use Illuminate\Database\Migrations\Migration;

class FixBugForSmsRecipients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $replies = \App\SmsThreadReply::where('created_at', '<', '2018-12-07 00:00:00')->get();
        $replies->each(function(\App\SmsThreadReply $reply) {
            $matchingPhone = PhoneNumber::where('national_number', $reply->from_number)->first();
            $user_id = optional($matchingPhone)->user_id;
            $reply->update(['user_id' => $user_id]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
