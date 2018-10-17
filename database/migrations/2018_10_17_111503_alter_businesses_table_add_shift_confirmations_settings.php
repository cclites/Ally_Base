<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBusinessesTableAddShiftConfirmationsSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('allow_client_confirmations')->default(1);
            $table->boolean('auto_confirm_modified')->default(1);
            $table->boolean('shift_confirmation_email')->default(1);
            $table->boolean('sce_shifts_in_progress')->default(1);
            $table->boolean('charge_diff_email')->default(0);
            $table->boolean('auto_append_hours')->default(1);
            $table->boolean('auto_confirm_unmodified_shifts')->default(0);
            $table->boolean('auto_confirm_verified_shifts')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'allow_client_confirmations',
                'auto_confirm_modified',
                'shift_confirmation_email',
                'sce_shifts_in_progress',
                'charge_diff_email',
                'auto_append_hours',
                'auto_confirm_unmodified_shifts',
                'auto_confirm_verified_shifts',
            ]);
        });
    }
}
