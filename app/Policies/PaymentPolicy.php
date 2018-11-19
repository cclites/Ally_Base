<?php

namespace App\Policies;

use App\Payment;
use App\User;

/**
 * Class PaymentPolicy
 * @package App\Policies
 *
 * TODO: This needs to check for client payment access as well as business payment access
 */
class PaymentPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        return false;
    }

    public function read(User $user, Payment $payment)
    {
        return $this->businessCheck($user, $payment);
    }

    public function update(User $user, Payment $payment)
    {
        return $user->role_type === 'admin';
    }

    public function delete(User $user, Payment $payment)
    {
        return $user->role_type === 'admin';
    }
}
