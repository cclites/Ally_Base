<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientOnboardingActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_onboarding_activities', function (Blueprint $table) {
            $table->unsignedInteger('client_onboarding_id');
            $table->unsignedInteger('onboarding_activity_id');
            $table->string('assistance_level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('client_onboarding_activities');
    }
}
