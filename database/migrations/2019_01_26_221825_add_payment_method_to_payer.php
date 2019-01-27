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
    }
}
