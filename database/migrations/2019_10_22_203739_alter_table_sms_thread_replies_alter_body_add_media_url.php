<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSmsThreadRepliesAlterBodyAddMediaUrl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_thread_replies', function (Blueprint $table) {
            $table->text('message', 255)->nullable()->change();
            $table->string('media_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_thread_replies', function (Blueprint $table) {
            $table->text('message', 255)->nullable(false)->change();
            $table->dropColumn('media_url');
        });
    }
}
