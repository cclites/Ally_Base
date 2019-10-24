<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableBusinessAddSettingOpenShift extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'businesses', function (Blueprint $table) {
            $table->enum('open_shifts_setting', [ 'off', 'limited', 'unlimited' ])->default( 'off' )->after( 'timezone' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'businesses', function (Blueprint $table) {
            $table->dropColumn('open_shifts_setting');
        });
    }
}
