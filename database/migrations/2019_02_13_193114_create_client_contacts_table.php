<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->string('name');
            $table->string('relationship', 50)->nullable();
            $table->string('relationship_custom')->nullable();
            $table->string('email')->nullable();
            $table->string('phone1', 45)->nullable();
            $table->string('phone2', 45)->nullable();
			$table->string('address')->nullable();
			$table->string('city', 45)->nullable();
			$table->string('state', 45)->nullable();
            $table->string('zip', 45)->nullable();

            $table->boolean('is_emergency')->default(0)->index();
            $table->unsignedTinyInteger('emergency_priority')->nullable()->index();
            
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_contacts');
    }
}
