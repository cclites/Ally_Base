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

            $table->foreign( 'caregiver_id' )->references( 'id' )->on( 'caregivers' )->onDelete( 'CASCADE' );
            $table->foreign( 'caregiver_invoice_id' )->references( 'id' )->on( 'caregiver_invoices' )->onDelete( 'CASCADE' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('occ_acc_deductibles');
    }
}
