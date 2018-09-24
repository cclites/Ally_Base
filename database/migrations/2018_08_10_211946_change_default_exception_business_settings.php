<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDefaultExceptionBusinessSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function(Blueprint $table) {
            $table->boolean('location_exceptions')->default(0)->change();
        });

        if (config('app.env') === 'production') {
            \App\Business::query()->update(['location_exceptions' => 0]);
            \App\SystemException::notAcknowledged()->where('reference_type', \App\Shift::class)->update(['acknowledged_at' => \Carbon\Carbon::now()]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
