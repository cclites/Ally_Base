<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReferralSourceIdForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreign('referral_source_id')->references('id')->on('referral_sources')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });

        Schema::table('caregivers', function (Blueprint $table) {
            $table->foreign('referral_source_id')->references('id')->on('referral_sources')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
        
        Schema::table('prospects', function (Blueprint $table) {
            $table->foreign('referral_source_id')->references('id')->on('referral_sources')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['referral_source_id']);
        });

        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropForeign(['referral_source_id']);
        });

        Schema::table('prospects', function (Blueprint $table) {
            $table->dropForeign(['referral_source_id']);
        });
    }
}
