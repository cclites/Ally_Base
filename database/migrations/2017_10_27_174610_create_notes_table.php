<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('caregiver_id')->nullable();
            $table->unsignedInteger('client_id')->nullable();
            $table->text('body');
            $table->string('tags', 32)->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('business_id');
            $table->timestamps();

            $table->foreign('caregiver_id')->references('id')->on('caregivers');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('business_id')->references('id')->on('businesses');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notes');
    }
}
