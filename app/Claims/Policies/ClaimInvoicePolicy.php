<?php

namespace App\Claims\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Policies\BasePolicy;
use App\Claims\ClaimInvoice;
use App\User;

class ClaimInvoicePolicy extends BasePolicy
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
        return false;
    }

    public function read(User $user, ClaimInvoice $claim)
    {
        return $this->businessCheck($user, $claim);
    }

    public function update(User $user, ClaimInvoice $claim)
    {
        return $this->businessCheck($user, $claim);
    }

    public function delete(User $user, ClaimInvoice $claim)
    {
        return $this->businessCheck($user, $claim);
    }
}
