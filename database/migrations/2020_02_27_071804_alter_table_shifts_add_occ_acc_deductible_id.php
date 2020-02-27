<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableShiftsAddOccAccDeductibleId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'shifts', function ( Blueprint $table ) {

            $table->unsignedInteger( 'occ_acc_deductible_id' )->nullable();

            $table->foreign( 'occ_acc_deductible_id' )->references( 'id' )->on( 'occ_acc_deductibles' )->onDelete( 'CASCADE' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'shifts', function ( Blueprint $table ) {

            $table->dropColumn([ 'occ_acc_deductible_id' ]);
        });
    }
}
