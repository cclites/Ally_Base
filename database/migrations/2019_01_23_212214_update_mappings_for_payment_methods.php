<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMappingsForPaymentMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        \App\Billing\GatewayTransaction::where('method_type', 'App\CreditCard')->update(['method_type' => 'credit_cards']);
        \App\Billing\GatewayTransaction::where('method_type', 'App\BankAccount')->update(['method_type' => 'bank_accounts']);

        \App\Client::where('default_payment_type', 'App\BankAccount')->update(['default_payment_type' => 'bank_accounts']);
        \App\Client::where('backup_payment_type', 'App\BankAccount')->update(['backup_payment_type' => 'bank_accounts']);

        \App\Client::where('default_payment_type', 'App\CreditCard')->update(['default_payment_type' => 'credit_cards']);
        \App\Client::where('backup_payment_type', 'App\CreditCard')->update(['backup_payment_type' => 'credit_cards']);

        \App\Client::where('default_payment_type', 'App\Business')->update(['default_payment_type' => 'businesses']);
        \App\Client::where('backup_payment_type', 'App\Business')->update(['backup_payment_type' => 'businesses']);
        DB::commit();
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
