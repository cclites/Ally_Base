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
            $table->string('contact_address_street', 150);
            $table->string('contact_address_street2', 50)->nullable();
            $table->string('contact_address_city', 100);
            $table->string('contact_address_state', 50);
            $table->string('contact_address_zip', 10);
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
