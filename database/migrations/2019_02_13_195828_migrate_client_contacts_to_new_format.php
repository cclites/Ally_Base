<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Client;
use App\EmergencyContact;
use App\ClientContact;

class MigrateClientContactsToNewFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // **********************************************************
        // Convert existing client emergency contacts
        // **********************************************************
        
        $clientIds = Client::select('id')->get()->pluck('id');

        foreach (\DB::table('emergency_contacts')->whereIn('user_id', $clientIds)->get() as $contact) {
            ClientContact::create([
                'client_id' => $contact->user_id,
                'name' => $contact->name,
                'phone1' => $contact->phone_number,
                'relationship' => ClientContact::RELATION_CUSTOM,
                'relationship_custom' => $contact->relationship,
                'is_emergency' => true,
                'emergency_priority' => $contact->priority,
            ]);
        }

        \DB::table('emergency_contacts')->whereIn('user_id', $clientIds)->delete();

        // **********************************************************
        // Convert existing client poa and physician fields 
        // **********************************************************
        
        foreach (Client::all() as $client) {
            if (isset($client->dr_first_name) || isset($client->dr_last_name)) {
                $client->contacts()->create([
                    'name' => $client->dr_first_name.' '.$client->dr_last_name,
                    'phone1' => $client->dr_phone,
                    'phone2' => $client->dr_fax,
                    'relationship' => ClientContact::RELATION_PHYSICIAN,
                ]);
            }

            if (isset($client->poa_first_name) || isset($client->poa_last_name)) {
                $client->contacts()->create([
                    'name' => $client->poa_first_name.' '.$client->poa_last_name,
                    'phone1' => $client->poa_phone,
                    'email' => $client->poa_email,
                    'relationship' => ClientContact::RELATION_POA,
                ]);
            }
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('poa_first_name');
            $table->dropColumn('poa_last_name');
            $table->dropColumn('poa_phone');
            $table->dropColumn('poa_email');
            $table->dropColumn('poa_relationship');
            $table->dropColumn('dr_first_name');
            $table->dropColumn('dr_last_name');
            $table->dropColumn('dr_phone');
            $table->dropColumn('dr_fax');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('poa_first_name', 50)->nullable()->after('ambulatory');
            $table->string('poa_last_name', 50)->nullable()->after('poa_first_name');
            $table->string('poa_phone', 25)->nullable()->after('poa_last_name');
            $table->string('poa_email', 128)->nullable()->after('poa_phone');
            $table->string('poa_relationship', 100)->nullable()->after('poa_phone');

            $table->string('dr_first_name', 50)->nullable()->after('import_identifier');
            $table->string('dr_last_name', 50)->nullable()->after('dr_first_name');
            $table->string('dr_phone', 25)->nullable()->after('dr_last_name');
            $table->string('dr_fax', 25)->nullable()->after('dr_phone');
        });

        foreach (ClientContact::all() as $contact) {
            if ($contact->relationship == ClientContact::RELATION_PHYSICIAN) {
                $contact->client->update([
                    'dr_first_name' => $contact->first_name,
                    'dr_last_name' => $contact->last_name,
                    'dr_phone' => $contact->phone1,
                    'dr_fax' => $contact->phone2,
                ]);
            } else if ($contact->relationship == ClientContact::RELATION_POA) {
                $contact->client->update([
                    'poa_first_name' => $contact->first_name,
                    'poa_last_name' => $contact->last_name,
                    'poa_phone' => $contact->phone1,
                    'poa_email' => $contact->email,
                    'poa_relationship' => null,
                ]);
            } else {
                $contact->client->user->emergencyContacts()->create([
                    'name' => $contact->name,
                    'phone_number' => $contact->phone1,
                    'relationship' => $contact->relationship == ClientContact::RELATION_CUSTOM ? $contact->relationship_custom : $contact->relationship,
                    'priority' => $contact->emergency_priority ?? 1,
                ]);
            }
        }

        ClientContact::whereRaw('1')->delete();
    }
}
