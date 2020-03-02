<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableCaregiversAddCertificateNumberColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'caregivers', function ( Blueprint $table ) {

            $table->string( 'certificate_number', 50 )->after( 'has_occ_acc' )->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'caregivers', function ( Blueprint $table ) {

            $table->dropColumn([ 'certificate_number' ]);
        });
    }
}
