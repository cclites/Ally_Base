<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientCareTableAddPhysiologicalInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_care_details', function (Blueprint $table) {
            $table->text('height')->nullable();
            $table->text('weight')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_care_details', function (Blueprint $table) {
            $table->dropColumn('height');
            $table->dropColumn('weight');
        });
    }
}
