<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\CareDetails;

class CreateClientCareDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_care_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id')->unique();
            
            $table->boolean('lives_alone')->default(0);
            $table->string('pets', 100)->nullable();
            $table->boolean('smoker')->default(0);
            $table->boolean('alcohol')->default(0);
            $table->boolean('incompetent')->default(0);
            $table->enum('competency_level', CareDetails::COMPETENCY_LEVELS)->nullable();
            $table->boolean('can_provide_direction')->default(0);
            $table->boolean('assist_medications')->default(0);
            $table->text('medication_overseer')->nullable();
            $table->string('safety_measures', 200)->nullable();
            $table->text('safety_instructions')->nullable();
            $table->string('mobility', 255)->nullable();
            $table->text('mobility_instructions')->nullable();
            $table->string('toileting', 200)->nullable();
            $table->text('toileting_instructions')->nullable();
            $table->string('bathing', 200)->nullable();
            $table->text('bathing_frequency')->nullable();
            $table->text('bathing_instructions')->nullable();
            $table->enum('vision', CareDetails::VISION)->nullable();
            $table->enum('hearing', CareDetails::HEARING)->nullable();
            $table->text('hearing_instructions')->nullable();
            $table->string('diet', 255)->nullable();
            $table->text('diet_likes')->nullable();
            $table->text('feeding_instructions')->nullable();
            $table->string('skin', 100)->nullable();
            $table->text('skin_conditions')->nullable();
            $table->string('hair', 100)->nullable();
            $table->text('hair_frequency')->nullable();
            $table->string('oral', 100)->nullable();
            $table->enum('shaving', CareDetails::SHAVING)->nullable();
            $table->text('shaving_instructions')->nullable();
            $table->string('nails', 100)->nullable();
            $table->string('dressing', 100)->nullable();
            $table->text('dressing_instructions')->nullable();
            $table->string('housekeeping', 255)->nullable();
            $table->text('housekeeping_instructions')->nullable();
            $table->string('errands', 255)->nullable();
            $table->string('supplies', 255)->nullable();
            $table->text('supplies_instructions')->nullable();
            $table->text('comments')->nullable();
            $table->text('instructions')->nullable();

            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_care_details');
    }
}
