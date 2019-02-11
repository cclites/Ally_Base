<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableClientCareDetailsAddPharmacy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_care_details', function (Blueprint $table) {
            $table->text('allergies')->nullable()->after('medication_overseer');
            $table->text('pharmacy_name')->nullable()->after('allergies');
            $table->text('pharmacy_number')->nullable()->after('pharmacy_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_care_details', function (Blueprint $table) {
            $table->dropColumn('allergies');
            $table->dropColumn('pharmacy_name');
            $table->dropColumn('pharmacy_number');
        });
    }
}
