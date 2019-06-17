<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientInvoicesTableAddOfflineAmountPaidColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_invoices', function (Blueprint $table) {
            $table->decimal('offline_amount_paid', 9, 2)->default(0)->after('offline');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_invoices', function (Blueprint $table) {
            $table->dropColumn('offline_amount_paid');
        });
    }
}
