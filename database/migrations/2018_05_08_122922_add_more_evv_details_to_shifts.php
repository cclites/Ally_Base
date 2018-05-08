<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreEvvDetailsToShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->mediumInteger('checked_in_distance')->unsigned()->nullable()->after('checked_in_longitude')
                ->comment('The distance in meters from the client evv address.');
            $table->boolean('checked_in_verified')->default(0)->after('checked_in_distance');
            $table->mediumInteger('checked_out_distance')->unsigned()->nullable()->after('checked_out_longitude')
                ->comment('The distance in meters from the client evv address.');
            $table->boolean('checked_out_verified')->default(0)->after('checked_out_distance');
        });

        \App\Shift::chunk(100, function($shifts) {
            foreach($shifts as $shift) {
                if (!$shift->checked_in) {
                    // skip converted shifts
                    continue;
                }
                if ($shift->verified) {
                    $shift->checked_in_verified = true;
                    $shift->checked_out_verified = true;
                }
                if ($shift->checked_in_number) {
                    $shift->checked_in_verified = true;
                }
                if ($shift->checked_out_number) {
                    $shift->checked_out_verified = true;
                }
                if ($shift->checked_in_latitude || $shift->checked_in_longitude) {
                    if ($address = $shift->client->evvAddress) {
                        /** @var \App\Address $address */
                        $shift->checked_in_distance = $address->distanceTo($shift->checked_in_latitude, $shift->checked_in_longitude);
                        if ($shift->checked_in_distance === false) $shift->checked_in_distance = null;
                        if ($shift->checked_in_distance !== null && $shift->checked_in_distance <= \App\Shifts\ClockIn::MAXIMUM_DISTANCE_METERS) {
                            $shift->checked_in_verified = true;
                        }
                        $shift->checked_out_distance = $address->distanceTo($shift->checked_out_latitude, $shift->checked_out_longitude);
                        if ($shift->checked_out_distance === false) $shift->checked_out_distance = null;
                        if ($shift->checked_out_distance !== null && $shift->checked_out_distance <= \App\Shifts\ClockOut::MAXIMUM_DISTANCE_METERS) {
                            $shift->checked_out_verified = true;
                        }
                    }
                }

                // Save any changes
                $shift->save();
            }
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
            $table->dropColumn('checked_in_distance');
            $table->dropColumn('checked_in_verified');
            $table->dropColumn('checked_out_distance');
            $table->dropColumn('checked_out_verified');
        });
    }
}
