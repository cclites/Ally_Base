<?php

use App\Business;
use App\BusinessChain;
use App\CaregiverApplication;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeApplicationsOwnedByChain extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caregiver_applications', function (Blueprint $table) {
            $table->unsignedInteger('chain_id')->nullable();
            $table->foreign('chain_id')->references('id')->on('business_chains')->onDelete('cascade')->onUpdate('cascade');
        });

        CaregiverApplication::all()->each(function(CaregiverApplication $application) {
            $application->update([
                'chain_id' => Business::find($application->business_id)->chain_id,
            ]);
        });

        Schema::table('caregiver_applications', function (Blueprint $table) {
            $table->unsignedInteger('chain_id')->nullable(false)->change();
            if (\DB::getDriverName() != 'sqlite') {
                $table->dropForeign(['business_id']);
            }
            $table->dropColumn('business_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caregiver_applications', function (Blueprint $table) {
            $table->unsignedInteger('business_id')->nullable();
        });

        CaregiverApplication::all()->each(function(CaregiverApplication $application) {
            $application->update([
                'business_id' => BusinessChain::find($application->chain_id)->businesses()->first()->id,
            ]);
        });

        Schema::table('caregiver_applications', function (Blueprint $table) {
            $table->dropForeign(['chain_id']);
            $table->dropColumn('chain_id');
        });
    }
}
