<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixFkForEmergencyContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Emergency contacts need to be a cascading fk
        if (in_array(config('app.env'), ['staging', 'production'])) {
            DB::statement('ALTER TABLE `emergency_contacts`
	DROP FOREIGN KEY `emergency_contacts_user_id_foreign`;');
            DB::statement('ALTER TABLE `emergency_contacts`
	ADD CONSTRAINT `emergency_contacts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
