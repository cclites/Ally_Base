<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCaregiversRemoveCascadingBankAccountFk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caregivers', function (Blueprint $table) {
            if (\DB::getDriverName() != 'sqlite') {
                $table->dropForeign('fk_caregivers_bank_account_id');
            }
            $table->foreign('bank_account_id', 'fk_caregivers_bank_account_id')->references('id')->on('bank_accounts')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caregivers', function (Blueprint $table) {
            //
        });
    }
}
