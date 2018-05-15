<?php

use App\Shift;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCheckedMethodToShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('checked_in_method')->default('Unknown')->after('checked_in');
            $table->string('checked_out_method')->default('Unknown')->after('checked_in_number');
        });

        Shift::chunk(100, function($shifts) {
            foreach ($shifts as $shift) {
                if (!$shift->checked_in) {
                    $shift->checked_in_method = Shift::METHOD_CONVERTED;
                    $shift->checked_out_method = Shift::METHOD_CONVERTED;
                }
                else {
                    if ($shift->checked_in_number) {
                        $shift->checked_in_method = Shift::METHOD_TELEPHONY;
                    }
                    else {
                        $shift->checked_in_method = Shift::METHOD_GEOLOCATION;
                    }
                    if ($shift->checked_out_number) {
                        $shift->checked_out_method = Shift::METHOD_TELEPHONY;
                    }
                    else if ($shift->checked_out_latitude || $shift->checked_out_longitude) {
                        $shift->checked_out_method = Shift::METHOD_GEOLOCATION;
                    }
                    // Leave other clock outs as unknown
                }
                $shift->save();
            }
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('checked_in');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->boolean('checked_in');
        });

        Shift::chunk(100, function($shifts) {
            foreach ($shifts as $shift) {
                if ($shift->checked_in_method === Shift::METHOD_CONVERTED) {
                    $shift->checked_in = 0;
                }
                else {
                    $shift->checked_in = 1;
                }
                $shift->save();
            }
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('checked_in_method');
            $table->dropColumn('checked_out_method');
        });
    }
}
