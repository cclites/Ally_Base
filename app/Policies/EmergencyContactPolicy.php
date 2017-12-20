<?php

namespace App\Policies;

use App\User;
use App\EmergencyContact;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmergencyContactPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the emergencyContact.
     *
     * @param  \App\User  $user
     * @param  \App\EmergencyContact  $emergencyContact
     * @return mixed
     */
    public function view(User $user, EmergencyContact $emergencyContact)
    {
        $owner = $user->id === $emergencyContact->user_id;
        $office_user = $user->role_type == 'office_user';

        return $owner || $office_user;
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
        $owner = $user->id === $emergencyContact->user_id;
        $office_user = $user->role_type == 'office_user';

        return $owner || $office_user;
    }
}
