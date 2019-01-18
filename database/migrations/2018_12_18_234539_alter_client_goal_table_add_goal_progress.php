<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientGoalTableAddGoalProgress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_goals', function (Blueprint $table) {
            $table->boolean('track_goal_progress')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_goals', function (Blueprint $table) {
            $table->dropColumn('track_goal_progress');
        });
    }
}
