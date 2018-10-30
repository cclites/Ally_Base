<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCaregiverMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caregiver_meta', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('caregiver_id');
            $table->string('key', 32);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('caregiver_id')->references('id')->on('caregivers')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['caregiver_id', 'key']);
        });

        Schema::create('client_meta', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->string('key', 32);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['client_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caregiver_meta');
        Schema::dropIfExists('client_meta');
    }
}
