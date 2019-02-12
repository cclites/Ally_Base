<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Client;

class AlterUsersTableAddSetupStatusColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('setup_status', 25)->nullable()->index();
            $table->timestamp('welcome_email_sent_at')->nullable()->after('updated_at');
        });

        foreach (Client::with('defaultPayment')->get() as $client) {
            if ($client->agreement_status === Client::NEEDS_AGREEMENT) {
                $client->setup_status = Client::SETUP_NONE; 
            } else if (! empty($client->defaultPayment)) {
                $client->setup_status = Client::SETUP_ADDED_PAYMENT;
                $client->setupStatusHistory()->create(['status' => Client::SETUP_ADDED_PAYMENT]);
            } else {
                $client->setup_status = Client::SETUP_CREATED_ACCOUNT;
                $client->setupStatusHistory()->create(['status' => Client::SETUP_CREATED_ACCOUNT]);
            }
            $client->welcome_email_sent_at = $client->email_sent_at;
            $client->save();
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_sent_at']);
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
            $table->dropColumn(['setup_status']);
            $table->timestamp('email_sent_at')->nullable()->after('updated_at');
        });

        foreach (Client::all() as $client) {
            $client->email_sent_at = $client->welcome_email_sent_at;
            $client->save();
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['welcome_email_sent_at']);
        });
    }
}
