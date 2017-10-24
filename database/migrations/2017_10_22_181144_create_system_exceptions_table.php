<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemExceptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_exceptions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->unsignedInteger('acknowledged_by')->nullable();
            $table->unsignedInteger('business_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses');
            $table->foreign('acknowledged_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_exceptions');
    }
}
