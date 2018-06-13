<?php

namespace App\Policies;

use App\PhoneNumber;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PhoneNumberPolicy extends BasePolicy
{

    public function create(User $user, $data)
    {
        $phone = new PhoneNumber($data);
        return $this->check($user, $phone);
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
        return $this->check($user, $phone);
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
        return $this->check($user, $phone);
    }

    protected function check(User $user, PhoneNumber $phone)
    {
        if ($phone->user_id != $user->id) {
            if (!$this->isAdmin() && !$this->businessHasUser($phone->user)) {
                return false;
            }
        }

        return true;
    }
}
