<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAddPayerToCaregiver1099s extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'caregiver_1099s', function ( Blueprint $table ) {
            $table->string( 'payer', 20 )->after( 'modified_by' )->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'caregiver_1099s', function ( Blueprint $table ) {
            $table->dropColumn( ['payer'] );
        });
    }
}
