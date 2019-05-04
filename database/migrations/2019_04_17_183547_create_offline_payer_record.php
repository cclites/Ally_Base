<?php

use App\Billing\Payer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfflinePayerRecord extends Migration
{
    private $name = "OFFLINE";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('payers')->insert([
            'id' => Payer::OFFLINE_PAY_ID,
            'name' => $this->name,
            'week_start' => 1,
            'chain_id' => null,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $payer = Payer::find(Payer::OFFLINE_PAY_ID);
        if ($payer->name === $this->name) {
            $payer->delete();
        }
    }
}
