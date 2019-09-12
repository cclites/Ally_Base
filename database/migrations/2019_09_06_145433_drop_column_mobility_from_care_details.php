<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnMobilityFromCareDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_care_details', function($table) {
            $table->dropColumn(['mobility','mobility_instructions']);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_care_details', function($table) {
            $table->string('mobility', 255)->nullable();
            $table->string('mobility_instructions', 255)->nullable();
        });
    }
}
