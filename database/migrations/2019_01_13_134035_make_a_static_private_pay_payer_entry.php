<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeAStaticPrivatePayPayerEntry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payers', function (Blueprint $table) {
            $table->unsignedInteger('chain_id')->nullable()->change();
        });

        $payer = \App\Billing\Payer::create([
            'name' => 'Private Pay',
            'week_start' => 1,
            'chain_id' => null,
        ]);
        $payer->id = 0;
        $payer->save();

        \App\Billing\ClientPayer::whereNull('payer_id')->update(['payer_id' => 0]);

        Schema::table('client_payers', function (Blueprint $table) {
            $table->unsignedInteger('payer_id')->nullable(false)->default(0)->change();
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('payer_id')->references('id')->on('payers')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
