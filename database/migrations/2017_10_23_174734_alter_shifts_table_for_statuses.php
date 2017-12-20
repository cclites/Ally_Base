<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftsTableForStatuses extends Migration
{
    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function(Blueprint $table) {
            $table->string('status')->nullable();
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('paid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->boolean('paid')->default(0);
        });
    }
}
