<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\OfficeUser;

class CreateUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('key')->index();
            $table->boolean('sms')->default(0);
            $table->boolean('email')->default(0);
            $table->boolean('system')->default(0);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });

        // ======================================================
        // implement defaults for all office user notifications
        // ======================================================
        foreach(OfficeUser::all() as $user) {
            foreach (OfficeUser::$availableNotifications as $cls) {
                $user->notificationPreferences()->create([
                    'key' => $cls::getKey(),
                    'sms' => false,
                    'email' => false,
                    'system' => true,
                ]);
            }
        }

        // ======================================================
        // implement defaults for all caregiver notifications
        // ======================================================
        // TODO: implement
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_notification_preferences');
    }
}
