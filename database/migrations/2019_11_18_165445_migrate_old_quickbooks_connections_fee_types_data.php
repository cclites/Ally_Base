<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateOldQuickbooksConnectionsFeeTypesData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('quickbooks_connections')->get()->each(function ($item) {
            \DB::table('quickbooks_connections')->where('id', $item->id)
                ->update([
                    'fee_type_lead_agency' => $item->fee_type,
                    'fee_type_ltci' => $item->fee_type,
                    'fee_type_medicaid' => $item->fee_type,
                    'fee_type_private_pay' => $item->fee_type,
                    'fee_type_va' => $item->fee_type,
                ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('quickbooks_connections')->get()->each(function ($item) {
            \DB::table('quickbooks_connections')->where('id', $item->id)
                ->update([
                    'fee_type_lead_agency' => \App\QuickbooksConnection::FEE_TYPE_REGISTRY,
                    'fee_type_ltci' => \App\QuickbooksConnection::FEE_TYPE_REGISTRY,
                    'fee_type_medicaid' => \App\QuickbooksConnection::FEE_TYPE_REGISTRY,
                    'fee_type_private_pay' => \App\QuickbooksConnection::FEE_TYPE_REGISTRY,
                    'fee_type_va' => \App\QuickbooksConnection::FEE_TYPE_REGISTRY,
                ]);
        });
    }
}
