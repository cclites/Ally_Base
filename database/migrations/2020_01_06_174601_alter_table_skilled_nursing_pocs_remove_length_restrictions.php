<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSkilledNursingPocsRemoveLengthRestrictions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'skilled_nursing_pocs', function ( Blueprint $table ) {
            $table->text('mobility_instructions')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'skilled_nursing_pocs', function ( Blueprint $table ) {
            $table->string('mobility_instructions', 255)->nullable()->change();
        });
    }
}
