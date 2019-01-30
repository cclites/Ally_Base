<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeactivationReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deactivation_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('chain_id')->nullable();
            $table->string('type', 10)->index();
            $table->timestamps();

            $table->foreign('chain_id')->references('id')->on('business_chains');
        });

        $this->seedFactoryReasonCodes();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deactivation_reasons', function (Blueprint $table) {
            $table->dropForeign(['chain_id']);
        });
        Schema::dropIfExists('deactivation_reasons');
    }

    public function seedFactoryReasonCodes()
    {
        $clientReasons = collect([
            'Deceased',
            'Temporary Hospital Stay',
            'Cancelled Service'
        ]);

        $caregiverReasons = collect([
            'Certification / License Expired',
            'Contractor does not want to work',
            'Contractor is not available'
        ]);

        $clientReasons->each(function ($item) {
            \App\DeactivationReason::create([
                'name' => $item,
                'type' => 'client',
            ]);
        });

        $caregiverReasons->each(function ($item) {
            \App\DeactivationReason::create([
                'name' => $item,
                'type' => 'caregiver',
            ]);
        });
    }
}
