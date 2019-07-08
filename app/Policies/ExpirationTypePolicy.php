<?php

namespace App\Policies;

use App\ExpirationType;
use App\User;

class ExpirationTypePolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $field = new ExpirationType($data);
        return $this->businessChainCheck($user, $field);
    }

    public function read(User $user, ExpirationType $field)
    {
        return $this->businessChainCheck($user, $field);
    }

    public function update(User $user, ExpirationType $field)
    {
        return $this->businessChainCheck($user, $field);
    }

    public function delete(User $user, ExpirationType $field)
    {
        return $this->businessChainCheck($user, $field);
    }
}