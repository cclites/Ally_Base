<?php

namespace App\Policies;

use App\Billing\GatewayTransaction;
use App\User;

/**
 * Class GatewayTransactionPolicy
 * @package App\Policies
 */
class GatewayTransactionPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        return false;
    }

    public function read(User $user, GatewayTransaction $transaction)
    {
        return $this->check($user, 'read', $transaction);
    }

    public function update(User $user, GatewayTransaction $transaction)
    {
        return $this->check($user, 'update', $transaction);
    }

    public function delete(User $user, GatewayTransaction $transaction)
    {
        return $this->check($user, 'delete', $transaction);
    }

    protected function check(User $user, $ability, GatewayTransaction $transaction)
    {
        if ($transaction->payment) {
            return $user->can($ability, $transaction->payment);
        }

        if ($transaction->deposit) {
            return $user->can($ability, $transaction->deposit);
        }

        return $user->role_type === 'admin';
    }
}
