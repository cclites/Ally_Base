<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Client;

class AlterClientsTableAddAccountSetupFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('agreement_status', 25)->nullable()->after('ssn')->index();
        });

        // convert statuses
        Client::whereIn('onboard_status', ['emailed_reconfirmation', 'needs_agreement'])
            ->update(['agreement_status' => Client::NEEDS_AGREEMENT]);
            
        Client::whereIn('onboard_status', ['agreement_signed'])
            ->update(['agreement_status' => Client::SIGNED_PAPER]);
        
        Client::whereIn('onboard_status', ['agreement_checkbox', 'reconfirmed_checkbox'])
            ->update(['agreement_status' => Client::SIGNED_ELECTRONICALLY]);

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('onboard_status');
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
            $table->string('onboard_status')->nullable()->after('ssn');
        });

        Client::where('agreement_status', Client::NEEDS_AGREEMENT)
            ->update(['onboard_status' => 'needs_agreement']);
            
        Client::where('agreement_status', Client::SIGNED_PAPER)
            ->update(['onboard_status' => 'agreement_signed']);
        
        Client::where('agreement_status', Client::SIGNED_ELECTRONICALLY)
            ->update(['onboard_status' => 'reconfirmed_checkbox']);

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['agreement_status']);
        });
    }
}
