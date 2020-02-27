<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOccAccDeductiblesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('occ_acc_deductibles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('caregiver_id');
            $table->unsignedInteger('caregiver_invoice_id');
            $table->decimal( 'amount', 9, 2 );
            $table->dateTime( 'week_start' );
            $table->dateTime( 'week_end' );
            $table->timestamps();

            $table->foreign( 'caregiver_id' )->references( 'id' )->on( 'caregivers' )->onDelete( 'RESTRICT' );
            $table->foreign( 'caregiver_invoice_id' )->references( 'id' )->on( 'caregiver_invoices' )->onDelete( 'RESTRICT' );
        });

        Schema::create('occ_acc_deductible_shifts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('deductible_id');
            $table->unsignedInteger('shift_id');
            $table->decimal( 'duration', 9, 2 );
            $table->decimal( 'amount', 9, 2 );
            $table->timestamps();

            $table->foreign( 'deductible_id' )->references( 'id' )->on( 'occ_acc_deductibles' )->onDelete( 'RESTRICT' );
            $table->foreign( 'shift_id' )->references( 'id' )->on( 'shifts' )->onDelete( 'RESTRICT' );
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
        Schema::dropIfExists('occ_acc_deductibles');
    }
}
