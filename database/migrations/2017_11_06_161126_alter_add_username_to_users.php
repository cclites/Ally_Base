<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;

class AlterAddUsernameToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('email_unique');
            $table->string('username', 128)->nullable()->after('email');
        });

        DB::statement('UPDATE users SET username = email');

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 128)->nullable(false)->change();
            $table->unique('username');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('email', 'email_unique');
            $table->dropColumn('username');
        });
    }
}
