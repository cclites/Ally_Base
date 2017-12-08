<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBusinessFeeToPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
//            $table->boolean('deposited')->default(0);
            $table->decimal('business_allotment', 9, 2)->default(0);
            $table->decimal('caregiver_allotment', 9, 2)->default(0);
            $table->decimal('system_allotment', 9, 2)->default(0);
        });

        Schema::table('payment_queue', function (Blueprint $table) {
            $table->decimal('business_allotment', 9, 2)->default(0);
            $table->decimal('caregiver_allotment', 9, 2)->default(0);
            $table->decimal('system_allotment', 9, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
