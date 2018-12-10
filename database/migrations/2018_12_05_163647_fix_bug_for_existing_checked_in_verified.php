<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixBugForExistingCheckedInVerified extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Shift::where('checked_in_time', '>', '2018-11-21 00:00:00')
            ->where('checked_in_method', 'Geolocation')
            ->where('checked_out_verified', 0)
            ->where('checked_in_verified', 1)
            ->where(function($q) {
                $q->whereNull('checked_in_distance')
                    ->orWhere('checked_in_distance', '>', 300);
            })
            ->update(['checked_in_verified' => 0]);

        \App\Shift::where('checked_in_time', '>', '2018-11-21 00:00:00')
            ->where('checked_in_method', 'Geolocation')
            ->where('checked_out_verified', 1)
            ->where('checked_in_verified', 1)
            ->where(function($q) {
                $q->whereNull('checked_in_distance')
                    ->orWhere('checked_in_distance', '>', 1000); // allow some leniency for existing verified shifts this time around
            })
            ->update(['checked_in_verified' => 0, 'verified' => 0]);
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
