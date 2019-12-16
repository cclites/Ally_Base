<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChainClientTypeSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chain_client_type_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_chain_id')->unsigned();
            $table->string('client_type');
            $table->string('medicaid_1099_default')->nullable();
            $table->boolean('medicaid_1099_can_edit')->default(false);
            $table->string('medicaid_1099_send_from')->default('client');

//            $table->string('medicaid_1099_default')->nullable();
//            $table->boolean('medicaid_1099_edit')->default(false);
//            $table->string('medicaid_1099_from')->nullable();
//            $table->string('private_pay_1099_default')->nullable();
//            $table->boolean('private_pay_1099_edit')->default(false);
//            $table->string('private_pay_1099_from')->nullable();
//            $table->string('other_1099_default')->nullable();
//            $table->boolean('other_1099_edit')->default(false);
//            $table->string('other_1099_from')->nullable();

            $table->foreign('business_chain_id')->references('id')->on('business_chains')->onDelete('CASCADE');
            $table->timestamps();
        });

        \App\BusinessChain::pluck("id")->each(function($id){
            \DB::table('chain_client_type_settings')->insert(['business_chain_id' => $id ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chain_client_type_settings');
    }
}
