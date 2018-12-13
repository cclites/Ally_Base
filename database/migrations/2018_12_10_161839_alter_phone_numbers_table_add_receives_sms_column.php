<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Caregiver;

class AlterPhoneNumbersTableAddReceivesSmsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('phone_numbers', function (Blueprint $table) {
            $table->boolean('receives_sms')->after('notes')->default(0);
        });

        $caregivers = Caregiver::has('phoneNumbers')
            ->with('phoneNumbers')
            ->get();

        foreach ($caregivers as $caregiver) {
            $number = $caregiver->phoneNumbers->where('type', 'mobile')->first();

            if (empty($number)) {
                $number = $caregiver->phoneNumbers->where('type', 'primary')->first();
            }

            if (empty($number)) {
                $number = $caregiver->phoneNumbers->first();
            }

            $number->update(['receives_sms' => true]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('phone_numbers', function (Blueprint $table) {
            $table->dropColumn('receives_sms');
        });
    }
}
