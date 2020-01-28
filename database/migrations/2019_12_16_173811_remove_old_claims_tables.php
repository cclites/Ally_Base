<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class RemoveOldClaimsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('hha_file_results');
        Schema::dropIfExists('hha_files');

        Schema::dropIfExists('tellus_file_results');
        Schema::dropIfExists('tellus_files');

        Schema::dropIfExists('claim_status_history');
        Schema::dropIfExists('claims');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No turning back
    }
}
