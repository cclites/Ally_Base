<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftsAddAddressId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->unsignedInteger('address_id')->nullable();
            $table->foreign('address_id')->references('id')->on('addresses')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        // if (app()->environment() === 'production') {
            \App\Shift::all()->each(function (\App\Shift $shift) {
                if ($client = $shift->client) {
                    if ($address = $client->addresses()->where('type', 'evv')->first()) {
                        $shift->update(['address_id' => $address->id]);
                    }
                }
            });
        // }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->dropColumn('address_id');
        });
    }
}
