<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_goals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shift_id');
            $table->unsignedInteger('client_goal_id');
            $table->text('comments');
            $table->timestamps();

            $table->unique(['shift_id', 'client_goal_id']);
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->foreign('client_goal_id')->references('id')->on('client_goals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift_goals');
    }
}
