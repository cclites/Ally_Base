<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatedConvertedSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('converted_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('schedule_id');
            $table->timestamp('checked_in_time');
            $table->timestamps();
            $table->index(['schedule_id', 'checked_in_time']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('converted_schedules');
    }
}
