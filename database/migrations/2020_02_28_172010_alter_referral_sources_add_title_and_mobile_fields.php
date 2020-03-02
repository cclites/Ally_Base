<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterReferralSourcesAddTitleAndMobileFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Expand work phone to accept larger numbers (because of extensions
        // Add title and mobile fields
        Schema::table('referral_sources', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->string('mobile_phone', 20)->nullable();
            $table->string( 'work_phone', 20 )->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referral_sources', function (Blueprint $table) {
            $table->dropColumn(['title', 'mobile_phone']);
            $table->string( 'work_phone', 15 )->nullable()->change();
        });
    }
}
