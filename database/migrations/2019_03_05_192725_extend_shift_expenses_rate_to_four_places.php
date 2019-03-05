<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtendShiftExpensesRateToFourPlaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shift_expenses', function (Blueprint $table) {
            $table->decimal('rate', 9, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shift_expenses', function (Blueprint $table) {
            $table->decimal('rate', 7, 2)->change();
        });
    }
}
