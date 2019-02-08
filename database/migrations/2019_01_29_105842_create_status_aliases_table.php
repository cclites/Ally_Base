<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusAliasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_aliases', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chain_id');
            $table->string('type')->index();
            $table->string('name');
            $table->boolean('active')->default(false);

            $table->timestamps();

            $table->foreign('chain_id')->references('id')->on('business_chains');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status_aliases', function (Blueprint $table) {
            $table->dropForeign(['chain_id']);
        });

        Schema::dropIfExists('status_aliases');
    }
}
