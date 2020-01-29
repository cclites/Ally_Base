<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTasksTableChangeAssignedUserIdForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (\DB::getDriverName() != 'sqlite') {
                $table->dropForeign(['assigned_user_id']);
            }
            $table->foreign('assigned_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('tasks', function (Blueprint $table) {
           $table->dropForeign(['assigned_user_id']);
           $table->foreign('assigned_user_id')->references('id')->on('office_users');
       });
    }
}
