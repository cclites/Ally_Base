<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientRsaAddClientSignatureText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_referral_service_agreements', function (Blueprint $table) {
            $table->string('signature_client_text');
            $table->ipAddress('executed_by_ip');
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
            $table->dropColumn('signature_client_text');
            $table->dropColumn('executed_by_ip');
        });
    }
}
