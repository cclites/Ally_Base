<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientRsaAddNameFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_referral_service_agreements', function (Blueprint $table) {
            $table->string('signature_one_text')->nullable();
            $table->string('signature_two_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_referral_service_agreements', function (Blueprint $table) {
            $table->dropColumn('signature_one_text');
            $table->dropColumn('signature_two_text');
        });
    }
}
