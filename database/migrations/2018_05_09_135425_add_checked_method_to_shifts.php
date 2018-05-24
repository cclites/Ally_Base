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
