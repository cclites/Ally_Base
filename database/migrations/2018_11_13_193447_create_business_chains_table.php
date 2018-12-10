<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessChainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_chains', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->char('country', 2)->default('US');
            $table->string('phone1', 45)->nullable();
            $table->string('phone2', 45)->nullable();

            // Extracted settings from businesses
            $table->boolean('scheduling')->default(1);

            $table->timestamps();
        });

        Schema::table('businesses', function(Blueprint $table) {
            $table->unsignedInteger('chain_id')->nullable();
            $table->foreign('chain_id')->references('id')->on('business_chains')->onDelete('restrict');
        });

        Schema::table('office_users', function(Blueprint $table) {
            $table->unsignedInteger('chain_id')->nullable();
            $table->foreign('chain_id')->references('id')->on('business_chains')->onDelete('restrict');
        });

        Schema::create('chain_caregivers', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chain_id');
            $table->unsignedInteger('caregiver_id');
            $table->timestamps();

            $table->foreign('chain_id')->references('id')->on('business_chains')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('caregiver_id')->references('id')->on('caregivers')->onDelete('cascade')->onUpdate('cascade');
        });


        // Migrate existing data into chains
        if (app()->environment() !== 'testing') {
            \App\Business::all()->each(function (\App\Business $business) {
                $chain = \App\BusinessChain::create($business->only(['name']));
                $business->chain()->associate($chain)->save();
                foreach($business->caregivers as $caregiver) {
                    $caregiver->businessChains()->attach($chain);
                }
                $business->users()->update(['chain_id' => $chain->id]);
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function(Blueprint $table) {
            $table->dropForeign(['chain_id']);
            $table->dropColumn('chain_id');
        });

        Schema::table('office_users', function(Blueprint $table) {
            $table->dropForeign(['chain_id']);
            $table->dropColumn('chain_id');
        });

        Schema::dropIfExists('chain_caregivers');
        Schema::dropIfExists('business_chains');
    }
}
