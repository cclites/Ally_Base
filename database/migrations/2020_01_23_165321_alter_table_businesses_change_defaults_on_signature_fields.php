<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterTableBusinessesChangeDefaultsOnSignatureFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table( 'businesses' )->where([ 'co_caregiver_signature' => 0, 'require_caregiver_signatures' => 1 ])->update([ 'require_caregiver_signatures' => 0 ]);
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean( 'require_caregiver_signatures' )->default( 0 )->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Dont ever reverse this
    }
}
