<?php

namespace App\Policies;

use App\Business;
use App\User;

class BusinessPolicy extends BasePolicy
{
    public function create(User $user, $data = [])
    {
        return $user->role_type === 'admin';
    }

    public function read(User $user, Business $business)
    {
        return $this->businessCheck($user, $business);
    }

    public function update(User $user, Business $business)
    {
        // Todo: Restrict to chain administrator
        return $this->businessCheck($user, $business);
    }

    public function delete(User $user, Business $business)
    {
        return $user->role_type === 'admin';
    }
}
