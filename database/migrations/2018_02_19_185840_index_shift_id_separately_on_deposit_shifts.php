<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexShiftIdSeparatelyOnDepositShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deposit_shifts', function (Blueprint $table) {
            $table->index('shift_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deposit_shifts', function (Blueprint $table) {
            $table->dropIndex('deposit_shifts_shift_id_index');
        });
    }
}
