<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPayersTableAddClaimsTransmissionFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payers', function (Blueprint $table) {
            $table->string('transmission_method')->nullable()->after('payment_method_id');
            $table->string('payer_code')->nullable()->after('transmission_method');
            $table->string('plan_code')->nullable()->after('payer_code');
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
            $table->dropColumn('transmission_method');
            $table->dropColumn('payer_code');
            $table->dropColumn('plan_code');
        });
    }
}
