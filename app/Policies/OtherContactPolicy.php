<?php

namespace App\Policies;

use App\User;
use App\OtherContact;

class OtherContactPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $contact = new OtherContact($data);
        return $this->businessCheck($user, $contact);
    }

    public function read(User $user, OtherContact $contact)
    {
        return $this->businessCheck($user, $contact);
    }

    public function update(User $user, OtherContact $contact)
    {
        return $this->businessCheck($user, $contact);
    }

    public function delete(User $user, OtherContact $contact)
    {
        return $this->businessCheck($user, $contact);
    }
}
