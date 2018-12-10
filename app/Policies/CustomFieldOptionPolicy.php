<?php

namespace App\Policies;

use App\User;
use App\CustomFieldOption;

class CustomFieldOptionPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $field = new CustomFieldOption($data);
        return $this->businessCheck($user, $field);
    }

    public function read(User $user, CustomFieldOption $field)
    {
        return $this->businessCheck($user, $field);
    }

    public function update(User $user, CustomFieldOption $field)
    {
        return $this->businessCheck($user, $field);
    }

    public function delete(User $user, CustomFieldOption $field)
    {
        return $this->businessCheck($user, $field);
    }
}
