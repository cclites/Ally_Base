<?php

namespace App\Policies;

use App\PhoneNumber;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PhoneNumberPolicy
{
    use HandlesAuthorization;

    public $fixed_types;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->fixed_types = collect(['primary', 'billing']);
    }

    /**
     * Determine if the given phone number can be updated by the user.
     *
     * @param  \App\User  $user
     * @param  \App\PhoneNumber  $phone
     * @return bool
     */
    public function update(User $user, PhoneNumber $phone)
    {
        $owner = $user->id === $phone->user_id;
        $office_user = $user->role_type == 'office_user';

        return ($owner || $office_user);
    }

    /**
     * Determine if the given phone number can be deleted by the user.
     *
     * @param  \App\User  $user
     * @param  \App\PhoneNumber  $phone
     * @return bool
     */
    public function delete(User $user, PhoneNumber $phone)
    {
        $owner = $user->id === $phone->user_id;
        $office_user = $user->role_type == 'office_user';

        return ($owner || $office_user) && !$this->fixed_types->contains($phone->type);
    }
}
