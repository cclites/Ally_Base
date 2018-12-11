<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_custom_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('chain_id')->unsigned();
            $table->text('user_type');
            $table->text('key');
            $table->text('label');
            $table->text('type');
            $table->boolean('required');
            $table->text('default_value')->nullable();
            $table->timestamps();

            $table->foreign('chain_id')->references('id')->on('business_chains')->onDelete('cascade');
            $table->foreign('key')->references('key')->on('caregiver_meta')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('key')->references('key')->on('client_meta')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_custom_fields');
    }
}
