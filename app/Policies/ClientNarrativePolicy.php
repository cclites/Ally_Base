<?php

namespace App\Policies;

use App\User;
use App\ClientNarrative;

class ClientNarrativePolicy extends BasePolicy
{
    /**
     * Determine whether the user can create clientNarratives.
     *
     * @param  \App\User  $user
     * @param  array  $data
     * @return bool
     */
    public function create(User $user, $data)
    {
        if ($user->active == 0) {
            return false;
        }

        $narrative = new ClientNarrative($data);

        return $this->caregiverClientCheck($user, $narrative->client)
                || $this->businessCheck($user, $narrative->client);
    }

    /**
     * Determine whether the user can update the clientNarrative.
     *
     * @param  \App\User  $user
     * @param  \App\ClientNarrative  $clientNarrative
     * @return bool
     */
    public function update(User $user, ClientNarrative $clientNarrative)
    {
        if ($user->active == 0) {
            return false;
        }

        return $this->caregiverClientCheck($user, $clientNarrative->client)
                || $this->businessCheck($user, $clientNarrative->client);
    }

    /**
     * Determine whether the user can delete the clientNarrative.
     *
     * @param  \App\User  $user
     * @param  \App\ClientNarrative  $clientNarrative
     * @return bool
     */
    public function delete(User $user, ClientNarrative $clientNarrative)
    {
        if ($user->active == 0) {
            return false;
        }

        return $this->caregiverClientCheck($user, $clientNarrative->client)
                || $this->businessCheck($user, $clientNarrative->client);
    }
}
