<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceableMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoiceable_meta', function (Blueprint $table) {
            $table->increments('id');
            $table->string('metable_type', 128);
            $table->unsignedInteger('metable_id');
            $table->string('key', 32);
            $table->text('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['metable_type', 'metable_id', 'key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoiceable_meta');
    }

}