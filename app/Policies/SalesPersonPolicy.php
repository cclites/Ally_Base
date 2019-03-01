<?php

namespace App\Policies;

use App\User;
use App\SalesPerson;

class SalesPersonPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $field = new SalesPerson($data);
        return $this->businessCheck($user, $field);
    }

    public function read(User $user, SalesPerson $field)
    {
        return $this->businessCheck($user, $field);
    }

    public function update(User $user, SalesPerson $field)
    {
        return $this->businessCheck($user, $field);
    }

    public function delete(User $user, SalesPerson $field)
    {
        return $this->businessCheck($user, $field);
    }
}
