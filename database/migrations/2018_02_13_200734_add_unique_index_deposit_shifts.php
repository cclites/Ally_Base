<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexDepositShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deposit_shifts', function (Blueprint $table) {
            $table->unique(['deposit_id', 'shift_id'], 'deposit_shifts_unique');
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
            $table->dropUnique(['deposit_shifts_unique']);
        });
    }
}
