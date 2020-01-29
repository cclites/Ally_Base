<?php

namespace App\Policies;

use App\Activity;
use App\User;

class ActivityPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $activity = new Activity($data);
        return $this->businessCheck($user, $activity);
    }

    public function read(User $user, Activity $activity)
    {
        return $this->businessCheck($user, $activity);
    }

    public function update(User $user, Activity $activity)
    {
        if (!$activity->business_id) {
            return $user->role_type === 'admin';
        }

        return $this->businessCheck($user, $activity);
    }

    public function delete(User $user, Activity $activity)
    {
        if (!$activity->business_id) {
            return $user->role_type === 'admin';
        }

        if ($activity->carePlans()->exists()) {
            return $this->deny('You cannot delete this activity because it is attached to a client\'s ADL Groups.');
        }

        return $this->businessCheck($user, $activity);
    }
}
