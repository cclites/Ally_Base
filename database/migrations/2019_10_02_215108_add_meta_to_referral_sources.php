<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMetaToReferralSources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'referral_sources', function ( Blueprint $table ) {

            $table->boolean( 'is_company' )->default( false )->nullable();
            $table->string( 'source_owner', 150 )->nullable();
            $table->string( 'source_type', 150 )->nullable();
            $table->string( 'web_address', 150 )->nullable();
            $table->string( 'work_phone', 15 )->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'referral_sources', function ( Blueprint $table ) {

            $table->dropColumn([
                'is_company',
                'source_owner',
                'source_type',
                'web_address',
                'work_phone'
            ]);
        });
    }
}
