<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;

class AlterUsersTableAddNotificationSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('allow_sms_notifications')->default(false);
            $table->boolean('allow_email_notifications')->default(true);
            $table->boolean('allow_system_notifications')->default(true);
            $table->string('notification_email')->nullable();
            $table->string('notification_phone')->nullable();
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
            $table->dropColumn([
                'allow_sms_notifications', 
                'allow_email_notifications', 
                'allow_system_notifications', 
                'notification_email', 
                'notification_phone'
            ]);
        });
    }
}
