<?php

namespace App\Policies;

use App\Caregiver;
use App\User;

class CaregiverPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $caregiver = new Caregiver($data);
        return $this->businessChainCheck($user, $caregiver);
    }

    public function read(User $user, Caregiver $caregiver)
    {
        return $this->businessChainCheck($user, $caregiver);
    }

    public function update(User $user, Caregiver $caregiver)
    {
        return $this->businessChainCheck($user, $caregiver);
    }

    public function delete(User $user, Caregiver $caregiver)
    {
        return $this->businessChainCheck($user, $caregiver);
    }
}
