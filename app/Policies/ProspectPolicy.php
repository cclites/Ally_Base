<?php

namespace App\Policies;

use App\Prospect;
use App\User;

class ProspectPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $prospect = new Prospect($data);
        return $this->businessCheck($user, $prospect);
    }

    public function read(User $user, Prospect $prospect)
    {
        return $this->businessCheck($user, $prospect);
    }

    public function update(User $user, Prospect $prospect)
    {
        return $this->businessCheck($user, $prospect);
    }

    public function delete(User $user, Prospect $prospect)
    {
        return $this->businessCheck($user, $prospect);
    }
}
