<?php

namespace App\Claims\Policies;

use App\Business;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\BasePolicy;
use App\Claims\ClaimRemit;
use App\User;

class ClaimRemitPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function create(User $user, $data)
    {
        return $this->businessCheck($user, Business::findOrFail($data['business_id']));
    }

    public function read(User $user, ClaimRemit $claimRemit)
    {
        return $this->businessCheck($user, $claimRemit);
    }

    public function update(User $user, ClaimRemit $claimRemit)
    {
        return $this->businessCheck($user, $claimRemit);
    }

    public function delete(User $user, ClaimRemit $claimRemit)
    {
        return $this->businessCheck($user, $claimRemit);
    }
}
