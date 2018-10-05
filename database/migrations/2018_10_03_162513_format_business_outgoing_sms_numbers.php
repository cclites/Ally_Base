<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Business;

class FormatBusinessOutgoingSmsNumbers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Business::all() as $business) {
            if (empty($business->outgoing_sms_number)) {
                continue;
            }

            $number = preg_replace('/[^0-9]/', '', $business->outgoing_sms_number);
            if (strlen($number) == 11) {
                $number = substr($number, 1);
            }

            $business->update(['outgoing_sms_number' => $number]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
