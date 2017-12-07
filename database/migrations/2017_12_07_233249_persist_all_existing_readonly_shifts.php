<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PersistAllExistingReadonlyShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $shifts = \App\Shift::whereReadOnly()->get();
        foreach($shifts as $shift) {
            if (!$shift->costs()->hasPersistedCosts()) {
                $shift->costs()->persist();
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
        // Do nothing
    }
}
