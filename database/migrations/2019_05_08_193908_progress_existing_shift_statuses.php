<?php

use App\Billing\Invoiceable\ShiftService;
use App\Billing\Queries\InvoiceableQuery;
use App\Shift;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProgressExistingShiftStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Moved to scripts/20190510_progress_existing_shift_statuses.php
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
