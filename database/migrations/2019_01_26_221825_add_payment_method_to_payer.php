<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentMethodToPayer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payers', function (Blueprint $table) {
            $table->unsignedInteger('payment_method_id')->after('fax_number')->nullable();
            $table->string('payment_method_type')->after('fax_number')->nullable();
        });

        Schema::table('client_payers', function (Blueprint $table) {
            $table->unsignedInteger('payment_method_id')->after('payer_id')->nullable();
            $table->string('payment_method_type')->after('payer_id')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('payment_method_id')->after('payment_type')->nullable();
            $table->string('payment_method_type')->after('payment_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payers', function (Blueprint $table) {
            $table->dropColumn(['payment_method_type', 'payment_method_id']);
        });

        Schema::table('client_payers', function (Blueprint $table) {
            $table->dropColumn(['payment_method_type', 'payment_method_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method_type', 'payment_method_id']);
        });
    }
}
