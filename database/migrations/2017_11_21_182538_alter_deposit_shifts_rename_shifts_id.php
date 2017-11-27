<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDepositShiftsRenameShiftsId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deposit_shifts', function (Blueprint $table) {
            $table->renameColumn('shifts_id', 'shift_id');
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
            $table->renameColumn('shift_id', 'shifts_id');
        });
    }
}
