<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftCostHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_cost_history', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->decimal('caregiver_shift', 9, 2);
            $table->decimal('caregiver_expenses', 9, 2);
            $table->decimal('caregiver_mileage', 9, 2);
            $table->decimal('caregiver_total', 9, 2);
            $table->decimal('provider_fee', 9, 2);
            $table->decimal('ally_fee', 9, 2);
            $table->decimal('total_cost', 9, 2);
            $table->decimal('ally_pct', 4, 4);
            $table->timestamps();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift_cost_history');
    }
}
