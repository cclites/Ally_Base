<?php

namespace App\Policies;

use App\Shift;
use App\User;

class ShiftPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $shift = new Shift($data);
        return $this->businessCheck($user, $shift);
    }

    public function read(User $user, Shift $shift)
    {
        if ($this->isClient()) {
            return $shift->client_id == $user->id;
        }

        return $this->businessCheck($user, $shift);
    }

    public function update(User $user, Shift $shift)
    {
        if ($this->isClient()) {
            return $shift->client_id == $user->id;
        }

        return $this->businessCheck($user, $shift)
            && (is_admin() || !$shift->isReadOnly());  // Only admins can modify read only shifts
    }

    public function delete(User $user, Shift $shift)
    {
        if ($this->isClient()) {
            return $shift->client_id == $user->id;
        }

        return $this->businessCheck($user, $shift)
            && (is_admin() || !$shift->isReadOnly());  // Only admins can modify read only shifts
    }
}
