<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterTableSmsThreadsAddFullTextIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if( !App::runningUnitTests() ) DB::statement('ALTER TABLE sms_threads ADD FULLTEXT fulltext_index (message);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if( !App::runningUnitTests() ) DB::statement('ALTER TABLE sms_threads DROP INDEX fulltext_index;');
    }
}
