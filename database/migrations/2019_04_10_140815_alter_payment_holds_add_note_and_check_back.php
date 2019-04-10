<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPaymentHoldsAddNoteAndCheckBack extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_holds', function (Blueprint $table) {
            $table->text('notes')->nullable();
            $table->date('check_back_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_holds', function (Blueprint $table) {
            $table->dropColumn('notes');
            $table->dropColumn('check_back_on');
        });
    }
}
