<?php
namespace App\Policies;

use App\Billing\Service;
use App\User;

class ServicePolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $service = new Service($data);
        return $this->businessChainCheck($user, $service);
    }

    public function read(User $user, Service $service)
    {
        return $this->businessChainCheck($user, $service);
    }

    public function update(User $user, Service $service)
    {
        return $this->businessChainCheck($user, $service);
    }

    public function delete(User $user, Service $service)
    {
        return $this->businessChainCheck($user, $service);
    }
}
