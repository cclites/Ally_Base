<?php

namespace App\Policies;

use App\Deposit;
use App\User;

/**
 * Class DepositPolicy
 * @package App\Policies
 *
 * TODO: This needs to check for caregiver deposits as well as business deposits
 */
class DepositPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        return false;
    }

    public function read(User $user, Deposit $payment)
    {
        return $this->businessCheck($user, $payment);
    }

    public function update(User $user, Deposit $payment)
    {
        return $user->role_type === 'admin';
    }

    public function delete(User $user, Deposit $payment)
    {
        return $user->role_type === 'admin';
    }
}
