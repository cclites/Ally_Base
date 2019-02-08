<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftsForServiceBreakouts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // shift_services and schedule_services in create_invoices migration

        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('client_rate', 8, 2)->default(0.0)->after('fixed_rates');
            $table->unsignedInteger('service_id')->nullable(); // Null when shifts have a service breakout or when we need to fallback to the business's default service
            $table->unsignedInteger('payer_id')->nullable(); // Null for auto
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedInteger('service_id')->nullable(); // Null when shifts have a service breakout or when we need to fallback to the business's default service
            $table->unsignedInteger('payer_id')->nullable(); // Null for auto
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['client_rate', 'service_id', 'payer_id']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['service_id', 'payer_id']);
        });
    }
}
