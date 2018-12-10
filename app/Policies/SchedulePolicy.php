<?php

namespace App\Policies;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Schedule;
use App\User;

class SchedulePolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $business = Business::find($data['business_id']);
        $caregiver = Caregiver::find($data['caregiver_id']);
        $client = Client::find($data['client_id']);

        return $user->can('read', $business)
            && (!$caregiver || $user->can('read', $caregiver))
            && $user->can('read', $client)
            && $business->id == $client->business_id;
    }

    public function read(User $user, Schedule $schedule)
    {
        return $this->businessCheck($user, $schedule);
    }

    public function update(User $user, Schedule $schedule)
    {
        return $this->businessCheck($user, $schedule);
    }

    public function delete(User $user, Schedule $schedule)
    {
        return $this->businessCheck($user, $schedule);
    }
}
