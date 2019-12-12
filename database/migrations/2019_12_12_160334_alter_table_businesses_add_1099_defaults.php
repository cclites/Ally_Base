<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableBusinessesAdd1099Defaults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'businesses', function ( Blueprint $table ) {
            $table->string( 'send_1099_default' )->nullable();
            $table->string( 'payer_1099_default' )->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'businesses', function ( Blueprint $table ) {
            $table->dropColumn( ['send_1099_default', 'payer_1099_default'] );
        });
    }
}
