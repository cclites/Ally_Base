<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use  App\Billing\Payments\PaymentDescriptionTypes;

class AlterAddDescriptionColumnToClaimPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_payments', function (Blueprint $table) {
            $table->string('description', 30)->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_payments', function (Blueprint $table) {
            $table->dropColumn(['description']);
        });
    }
}
