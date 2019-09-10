<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimRemitApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_remit_applications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('claim_remit_id')->nullable();
            $table->string('application_type', 30);
            $table->decimal('amount', 9, 2)->default(0.00);
            $table->boolean('is_interest')->default(false);

            $table->timestamps();

            $table->foreign('claim_remit_id')->references('id')->on('claim_remits')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claim_remit_applications');
    }
}
