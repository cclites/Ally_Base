<?php

namespace App\Policies;

use App\Client;
use App\User;

class ClientPolicy extends BasePolicy
{
    public function create(User $user, $data)
    {
        $client = new Client($data);
        return $this->businessCheck($user, $client);
    }

    public function read(User $user, Client $client)
    {
        return $this->businessCheck($user, $client);
    }

    public function update(User $user, Client $client)
    {
        return $this->businessCheck($user, $client);
    }

    public function delete(User $user, Client $client)
    {
        return $this->businessCheck($user, $client);
    }
}
