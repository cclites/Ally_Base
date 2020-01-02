<?php

use App\Billing\VisitEditActionEnum;
use App\Billing\VisitEditReasonEnum;
use App\VisitEditAction;
use App\VisitEditReason;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PopulateReasonCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach( VisitEditReasonEnum::fullList() as $reason ){

            VisitEditReason::create([

                'code' => $reason[ 'code' ],
                'description' => $reason[ 'description' ]
            ]);
        }

        foreach( VisitEditActionEnum::fullList() as $action ){

            VisitEditAction::create([

                'code' => $action[ 'code' ],
                'description' => $action[ 'description' ]
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
        VisitEditReason::truncate();
        VisitEditAction::truncate();
    }
}
