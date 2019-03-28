<?php

namespace App\Policies;

use App\Billing\Deposit;
use App\User;

/**
 * Class DepositPolicy
 * @package App\Policies
 *
 */
class DepositPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        return false;
    }

    public function read(User $user, Deposit $deposit)
    {
        return $deposit->caregiver_id == $user->id
            || ($deposit->business_id && $this->businessCheck($user, $deposit)) // For business deposits
            || ($deposit->caregiver && $user->can('view-caregiver-statements', $deposit->caregiver));
    }

    public function update(User $user, Deposit $deposit)
    {
        return $user->role_type === 'admin';
    }

    public function delete(User $user, Deposit $deposit)
    {
        return $user->role_type === 'admin';
    }
}
