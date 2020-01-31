<?php

namespace App\Policies;

use App\Caregiver1099;
use App\User;

class Caregiver1099Policy extends BasePolicy
{
    public function create(User $user, $data)
    {
        return $this->isAdmin();
    }

    public function read(User $user, Caregiver1099 $caregiver1099)
    {
        return $user->id == $caregiver1099->caregiver_id
            || $user->id == $caregiver1099->client_id
            || $this->businessCheck($user, $caregiver1099);
    }

    public function update(User $user, Caregiver1099 $caregiver1099)
    {
        return $user->id == $caregiver1099->caregiver_id
            || $user->id == $caregiver1099->client_id
            || $this->businessCheck($user, $caregiver1099);
    }

    public function delete(User $user, Caregiver1099 $caregiver1099)
    {
        return $this->isAdmin();
    }
}
