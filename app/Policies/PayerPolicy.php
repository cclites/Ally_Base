<?php
namespace App\Policies;

use App\Billing\Payer;
use App\User;

class PayerPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $payer = new Payer($data);
        return $this->businessChainCheck($user, $payer);
    }

    public function read(User $user, Payer $payer)
    {
        return $this->businessChainCheck($user, $payer);
    }

    public function update(User $user, Payer $payer)
    {
        return $this->businessChainCheck($user, $payer);
    }

    public function delete(User $user, Payer $payer)
    {
        return $this->businessChainCheck($user, $payer);
    }
}
