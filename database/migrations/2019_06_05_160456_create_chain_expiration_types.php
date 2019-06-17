<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChainExpirationTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chain_expiration_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->index();
            $table->unsignedInteger('chain_id')->nullable();
            $table->timestamps();

            $table->foreign('chain_id')->references('id')->on('business_chains')->onDelete('restrict')->onUpdate('cascade');
        });

        DB::table('chain_expiration_types')->insert([
            ['type'=>"Driver’s License"],
            ['type'=>'Auto Insurance'],
            ['type'=>'Car Registration'],
            ['type'=>'CNA license'],
            ['type'=>'CPR'],
            ['type'=>'CPR/AED'],
            ['type'=>'First Aid'],
            ['type'=>'Continuing Education Credits'],
            ['type'=>'Professional Liability Insurance'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chain_expiration_types');
    }
}
