<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCaregiversAddImportFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caregivers', function (Blueprint $table) {
            $table->date('hire_date')->nullable();
            $table->string('gender')->nullable();
            if (\DB::getDriverName() != 'sqlite') {
                $table->dropForeign('fk_caregivers_bank_account_id');
            }
            $table->foreign('bank_account_id', 'fk_caregivers_bank_account_id')->references('id')->on('bank_accounts')->onUpdate('CASCADE')->onDelete('CASCADE');
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
            $table->dropColumn('hire_date');
            $table->dropColumn('gender');
        });
    }
}
