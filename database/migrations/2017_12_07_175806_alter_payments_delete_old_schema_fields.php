<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPaymentsDeleteOldSchemaFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('fk_payments_caregiver_id');
            $table->dropColumn('caregiver_id');
            $table->dropColumn('deposited');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->integer('caregiver_id')->unsigned();
            $table->boolean('deposited')->default(0);
            $table->foreign('caregiver_id', 'fk_payments_caregiver_id')->references('id')->on('caregivers')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }
}
