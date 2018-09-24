<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBusinessesTableAddClockOutSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('co_mileage')->default(1);
            $table->boolean('co_injuries')->default(1);
            $table->boolean('co_comments')->default(1);
            $table->boolean('co_expenses')->default(1);
            $table->boolean('co_issues')->default(1);
            $table->boolean('co_signature')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('co_mileage');
            $table->dropColumn('co_injuries');
            $table->dropColumn('co_comments');
            $table->dropColumn('co_expenses');
            $table->dropColumn('co_issues');
            $table->dropColumn('co_signature');
        });
    }
}
