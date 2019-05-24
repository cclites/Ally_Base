<?php

namespace App\Policies;

use App\BusinessChain;
use App\User;

class BusinessChainPolicy extends BasePolicy
{
    public function create(User $user, $data = [])
    {
        return $user->role_type === 'admin';
    }

    public function read(User $user, BusinessChain $businessChain)
    {
        return $user->role_type === 'admin'
            || (int) optional($user->officeUser)->chain_id === $businessChain->id;
    }

    public function update(User $user, BusinessChain $businessChain)
    {
        // Todo: Restrict to chain administrator
        return $this->read($user, $businessChain);
    }

    public function delete(User $user, BusinessChain $businessChain)
    {
        return $user->role_type === 'admin';
    }
}
