<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRemoveDefaultsClientCareDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'client_care_details', function ( Blueprint $table ) {
            $table->boolean('lives_alone')->nullable()->default(null)->change();
            $table->boolean('smoker')->nullable()->default(null)->change();
            $table->boolean('alcohol')->nullable()->default(null)->change();
            $table->boolean('incompetent')->nullable()->default(null)->change();
            $table->boolean('can_provide_direction')->default(null)->nullable()->change();
            $table->boolean('assist_medications')->default(null)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'client_care_details', function ( Blueprint $table ) {
            $table->boolean('lives_alone')->default(0)->change();
            $table->boolean('smoker')->default(0)->change();
            $table->boolean('alcohol')->default(0)->change();
            $table->boolean('incompetent')->default(0)->change();
            $table->boolean('can_provide_direction')->default(0)->change();
            $table->boolean('assist_medications')->default(0)->change();
        });
    }
}

