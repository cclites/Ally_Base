<?php

namespace App\Policies;

use App\User;
use App\EmergencyContact;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmergencyContactPolicy extends BasePolicy
{
    /**
     * Determine whether the user can create an emergencyContact.
     *
     * @param User $user
     * @param array $data
     * @return void
     */
    public function create(User $user, $data)
    {
        return $user->active == 1;
    }

    /**
     * Determine whether the user can view the emergencyContact.
     *
     * @param  \App\User  $user
     * @param  \App\EmergencyContact  $emergencyContact
     * @return mixed
     */
    public function read(User $user, EmergencyContact $emergencyContact)
    {
        return $this->check($user, $emergencyContact);
    }

    /**
     * Determine whether the user can update the emergencyContact.
     *
     * @param User $user
     * @param EmergencyContact $emergencyContact
     * @return bool
     */
    public function update(User $user, EmergencyContact $emergencyContact)
    {
        if (! $user->active) {
            return false;
        }

        return $this->check($user, $emergencyContact);
    }

    /**
     * Determine whether the user can delete the emergencyContact.
     *
     * @param  \App\User  $user
     * @param  \App\EmergencyContact  $emergencyContact
     * @return mixed
     */
    public function delete(User $user, EmergencyContact $emergencyContact)
    {
        if (! $user->active) {
            return false;
        }

        return $this->check($user, $emergencyContact);
    }

    protected function check(User $user, EmergencyContact $emergencyContact)
    {
        if ($emergencyContact->user_id == $user->id) {
            return true;
        }

        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isOfficeUser() && $user->sharesBusinessWith($emergencyContact->user)) {
            return true;
        }

        return false;
    }
}
