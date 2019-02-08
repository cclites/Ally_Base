<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersTableAddDeactivationReasonId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('deactivation_reason_id')->nullable();
            $table->date('reactivation_date')->nullable();

            $table->foreign('deactivation_reason_id')->references('id')->on('deactivation_reasons')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['deactivation_reason_id']);
            $table->dropColumn('deactivation_reason_id');
            $table->dropColumn('reactivation_date');
        });
    }
}
