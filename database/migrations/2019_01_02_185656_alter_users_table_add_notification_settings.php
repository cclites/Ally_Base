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

        // ===========================================================
        // Set default values for user's noficiation email and phone
        // to their current email and (mobile) phone.
        // ===========================================================
        foreach (User::all() as $user) {
            if ($user->email && strpos($user->email, '@noemail.allyms.com') === false) {
                $user->notification_email = $user->email;
            }
            if ($phone = $user->phoneNumbers->where('type', 'mobile')->first()) {
                $user->notification_phone = $phone->national_number;
                $user->allow_sms_notifications = true;
            }
            $user->save();
        }
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
