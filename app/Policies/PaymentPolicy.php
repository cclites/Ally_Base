<?php

namespace App\Policies;

use App\Billing\Payment;
use App\User;

/**
 * Class PaymentPolicy
 * @package App\Policies
 *
 */
class PaymentPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        return false;
    }

    public function read(User $user, Payment $payment)
    {
        return $payment->client_id == $user->id
            || $this->businessCheck($user, $payment);
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
