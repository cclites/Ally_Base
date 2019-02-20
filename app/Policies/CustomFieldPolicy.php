<?php

namespace App\Policies;

use App\User;
use App\CustomField;

class CustomFieldPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $field = new CustomField($data);
        return $this->businessChainCheck($user, $field);
    }

    public function read(User $user, CustomField $field)
    {
        return $this->businessChainCheck($user, $field);
    }

    public function update(User $user, CustomField $field)
    {
        return $this->businessChainCheck($user, $field);
    }

    public function delete(User $user, CustomField $field)
    {
        return $this->businessChainCheck($user, $field);
    }
}
