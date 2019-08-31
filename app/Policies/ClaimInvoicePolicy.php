<?php

namespace App\Policies;

use App\Claims\ClaimInvoice;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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
