<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddTypeToCreditCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credit_cards', function (Blueprint $table) {
            $table->string('type')->after('name_on_card')->nullable();
        });

        foreach(\App\CreditCard::all() as $card) {
            $validator = \Inacho\CreditCard::validCreditCard($card->number);
            $card->update(['type' => $validator['type']]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credit_cards', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
