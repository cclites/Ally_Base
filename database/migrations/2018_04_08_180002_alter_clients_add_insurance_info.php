<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientsAddInsuranceInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('ltci_name')->nullable();
            $table->string('ltci_address')->nullable();
            $table->string('ltci_city', 60)->nullable();
            $table->string('ltci_state', 60)->nullable();
            $table->string('ltci_zip', 10)->nullable();
            $table->string('ltci_policy')->nullable();
            $table->string('ltci_claim')->nullable();
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
            $table->dropColumn('ltci_name');
            $table->dropColumn('ltci_address');
            $table->dropColumn('ltci_city');
            $table->dropColumn('ltci_state');
            $table->dropColumn('ltci_zip');
            $table->dropColumn('ltci_policy');
            $table->dropColumn('ltci_claim');
        });
    }
}
