<?php

namespace App\Policies;

use App\User;
use App\DeactivationReason;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeactivationReasonPolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create deactivationReasons.
     *
     * @param  \App\User  $user
     * @param array $data
     * @return mixed
     */
    public function create(User $user, $data)
    {
        return $this->isOfficeUser() || $this->isAdmin();
    }

    /**
     * Determine whether the user can read the deactivationReason.
     *
     * @param  \App\User  $user
     * @param  \App\DeactivationReason  $deactivationReason
     * @return mixed
     */
    public function read(User $user, DeactivationReason $deactivationReason)
    {
        return $this->businessChainCheck($user, $deactivationReason);
    }

    /**
     * Determine whether the user can update the deactivationReason.
     *
     * @param  \App\User  $user
     * @param  \App\DeactivationReason  $deactivationReason
     * @return mixed
     */
    public function update(User $user, DeactivationReason $deactivationReason)
    {
        return $this->businessChainCheck($user, $deactivationReason);
    }

    /**
     * Determine whether the user can delete the deactivationReason.
     *
     * @param  \App\User  $user
     * @param  \App\DeactivationReason  $deactivationReason
     * @return mixed
     */
    public function delete(User $user, DeactivationReason $deactivationReason)
    {
        return $this->businessChainCheck($user, $deactivationReason);
    }
}
