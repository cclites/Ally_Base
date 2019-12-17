<?php

namespace App\Policies;

use App\Billing\ClientInvoice;
use App\User;

/**
 * Class ClientInvoicePolicy
 * @package App\Policies
 *
 */
class ClientInvoicePolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        return false;
    }

    public function read(User $user, ClientInvoice $invoice)
    {
        if ($invoice->client_id == $user->id) {
            return $invoice->getClientPayer()->isPrivatePay() || $invoice->getClientPayer()->isOffline();
        }

        return $this->businessCheck($user, $invoice->client);
    }

    public function update(User $user, ClientInvoice $invoice)
    {
        return $user->role_type === 'admin';
    }

    public function delete(User $user, ClientInvoice $invoice)
    {
        return $user->role_type === 'admin';
    }
}
