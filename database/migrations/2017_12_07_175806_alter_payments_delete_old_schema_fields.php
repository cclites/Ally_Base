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
        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign('fk_payments_caregiver_id');
            });
        }
        catch(Exception $e) {}

        if (Schema::hasColumn('payments', 'caregiver_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('caregiver_id');
                $table->dropColumn('deposited');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
