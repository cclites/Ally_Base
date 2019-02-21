<?php

namespace App\Policies;

use App\ReferralSource;
use App\User;

class ReferralSourcePolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        return $this->isOfficeUser();
    }

    public function read(User $user, ReferralSource $source)
    {
        return $this->businessChainCheck($user, $source);
    }

    public function update(User $user, ReferralSource $source)
    {
        return $this->businessChainCheck($user, $source);
    }

    public function delete(User $user, ReferralSource $source)
    {
        return $this->businessChainCheck($user, $source);
    }
}
