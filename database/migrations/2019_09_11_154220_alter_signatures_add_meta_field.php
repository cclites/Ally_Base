<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSignaturesAddMetaField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'signatures', function ( Blueprint $table ) {

            $table->string( 'meta_type', 80 )->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'signatures', function ( Blueprint $table ) {

            $table->dropColumn( 'meta_type' );
        });
    }
}
