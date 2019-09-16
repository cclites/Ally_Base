<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClaimRemitTablesAddSoftDeletes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_remits', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('claim_remit_applications', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_remits', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('claim_remit_applications', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
