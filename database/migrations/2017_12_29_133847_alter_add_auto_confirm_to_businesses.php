<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddAutoConfirmToBusinesses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('auto_confirm')->default(0);
        });

        // Enable auto confirm for all existing businesses
        if (env('APP_ENV') === 'production') {
            foreach(\App\Business::all() as $business) {
                $business->update(['auto_confirm' => 1]);
            }
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
            $table->dropColumn('auto_confirm');
        });
    }
}
