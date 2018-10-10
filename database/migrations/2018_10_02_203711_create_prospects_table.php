<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProspectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email')->nullable();
            $table->string('client_type')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->char('country', 2)->default('US');
            $table->string('referred_by')->nullable();
            $table->date('last_contacted')->nullable();
            $table->date('initial_call_date')->nullable();
            $table->boolean('had_initial_call')->default(0);
            $table->boolean('had_assessment_scheduled')->default(0);
            $table->boolean('had_assessment_performed')->default(0);
            $table->boolean('needs_contract')->default(0);
            $table->boolean('expecting_client_signature')->default(0);
            $table->boolean('needs_payment_info')->default(0);
            $table->boolean('ready_to_schedule')->default(0);
            $table->boolean('closed_loss')->default(0);
            $table->boolean('closed_win')->default(0);
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('client_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prospects');
    }
}
