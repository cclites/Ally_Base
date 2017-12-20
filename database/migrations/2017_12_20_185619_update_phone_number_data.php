<?php

use App\User;
use Illuminate\Database\Migrations\Migration;

class UpdatePhoneNumberData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update primary phone number for clients and caregivers
        $users = User::with('phoneNumbers')
            ->whereHas('phoneNumbers')
            ->whereIn('role_type', ['client', 'caregiver'])
            ->get();

        foreach ($users as $user) {
            if ($user->phoneNumbers->count() == 1) {
                $user->phoneNumbers->first()->update(['type' => 'primary']);
            } else {
                switch ($user->role_type) {
                    case 'client':
                        $evv_number = $user->phoneNumbers->where('type', 'evv')->first();
                        if ($evv_number) {
                            $evv_number->update(['type' => 'primary']);
                        }
                        break;
                    case 'caregiver':
                        $work_number = $user->phoneNumbers->where('type', 'work')->first();
                        if ($work_number) {
                            $work_number->update(['type' => 'primary']);
                        }
                        break;
                }
            }
        }
    }
}
