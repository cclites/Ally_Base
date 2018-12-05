<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Business;

class AlterBusinessesUpdateConfirmSettingDefaults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('allow_client_confirmations');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('allow_client_confirmations')->default(1)->after('overtime_method');
        });

        if (app()->environment() !== 'production') {
            return;
        }

        foreach (Business::all() as $business) {
            $business->update([
                'auto_confirm_verified_shifts' => $business->auto_confirm,
                'auto_confirm' => 0,
                'shift_confirmation_email' => 0,
                'sce_shifts_in_progress' => 0,
                'auto_append_hours' => 0,
            ]);   
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('allow_client_confirmations');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('allow_client_confirmations')->default(1);
        });

        if (env('APP_ENV') !== 'production') {
            return;
        }

        foreach (Business::all() as $business) {
            $business->update([
                'auto_confirm_verified_shifts' => 0,
                'auto_confirm' => $business->auto_confirm_verified_shifts,
            ]);   
        }
    }
}
