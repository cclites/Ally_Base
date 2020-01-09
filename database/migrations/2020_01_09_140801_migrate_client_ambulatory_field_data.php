<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateClientAmbulatoryFieldData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('clients')
            ->where('ambulatory', '=', '1')
            ->update(['ambulatory' => \App\Client::AMBULATORY_PHYSICAL]);

        \DB::table('clients')
            ->where('ambulatory', '=', '0')
            ->orWhereNull('ambulatory')
            ->update(['ambulatory' => \App\Client::AMBULATORY_INDEPENDENT]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('clients')
            ->where('ambulatory', '=', \App\Client::AMBULATORY_PHYSICAL)
            ->update(['ambulatory' => 1]);

        \DB::table('clients')
            ->where('ambulatory', '=', \App\Client::AMBULATORY_INDEPENDENT)
            ->update(['ambulatory' => 0]);
    }
}
