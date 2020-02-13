<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterReferralSourcesAddContact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('referral_sources', function(Blueprint $table){
            $table->string('contact_address_street');
            $table->string('contact_address_street2')->nullable();
            $table->string('contact_address_city');
            $table->string('contact_address_state');
            $table->string('contact_address_zip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referral_sources', function(Blueprint $table){
            $table->dropColumn(
                'contact_address_street',
                'contact_address_street2','contact_address_city',
                'contact_address_state','contact_address_zip'
            );
        });
    }
}
