<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBusinessesTableAddOtAndHolSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->decimal('ot_multiplier', 2, 1)->default(1.5);
            $table->string('ot_behavior', 25)->nullable();
            $table->decimal('hol_multiplier', 2, 1)->default(1.5);
            $table->string('hol_behavior', 25)->nullable();
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
                'ot_multiplier',
                'ot_behavior',
                'hol_multiplier',
                'hol_behavior',
            ]);
        });
    }
}
