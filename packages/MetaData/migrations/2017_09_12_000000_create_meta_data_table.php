<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetaDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meta_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('metable_type', 128);
            $table->string('metable_id', 20);
            $table->string('key', 32);
            $table->text('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['metable_type', 'metable_id', 'key']);
        });
    }
}