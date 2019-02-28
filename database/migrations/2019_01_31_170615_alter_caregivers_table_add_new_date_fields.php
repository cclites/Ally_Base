<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCaregiversTableAddNewDateFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caregivers', function (Blueprint $table) {
            $table->date('application_date')->nullable();
            $table->date('orientation_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropColumn(['application_date', 'orientation_date']);
        });
    }
}
