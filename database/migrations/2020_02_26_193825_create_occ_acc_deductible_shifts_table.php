<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOccAccDeductibleShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('occ_acc_deductible_shifts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('occ_acc_deductible_id');
            $table->unsignedInteger('shift_id');
            $table->timestamps();

            $table->foreign( 'occ_acc_deductible_id' )->references( 'id' )->on( 'occ_acc_deductibles' )->onDelete( 'CASCADE' );
            $table->foreign( 'shift_id' )->references( 'id' )->on( 'shifts' )->onDelete( 'CASCADE' );

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('occ_acc_deductible_shifts');
    }
}
