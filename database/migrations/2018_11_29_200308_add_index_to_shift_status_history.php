<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToShiftStatusHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\ShiftStatusHistory::doesntHave('shift')->delete();

        Schema::table('shift_status_history', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shift_status_history', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });
    }
}
